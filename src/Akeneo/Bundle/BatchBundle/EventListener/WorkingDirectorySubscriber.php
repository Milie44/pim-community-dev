<?php

namespace Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\EventInterface;
use Akeneo\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Akeneo\Component\Batch\Job\UndefinedJobException;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\ItemStep;
use Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * If a step or step element of an item is {@link \Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface}, then:
 *   - the working directory is created before the execution of the job
 *     (event Akeneo\Component\Batch\Event\EventInterface\BEFORE_JOB_EXECUTION)
 *   - the working directory is deleted after the execution of the job
 *     (event Akeneo\Component\Batch\Event\EventInterface\AFTER_JOB_EXECUTION)
 *
 * The working directory is created in the temporary filesystem.
 *
 * Its pathname is placed in the JobExecutionContext via
 * the key {@link \Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface::CONTEXT_PARAMETER}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class WorkingDirectorySubscriber implements EventSubscriberInterface
{
    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * @param JobRegistry $jobRegistry
     */
    public function __construct(JobRegistry $jobRegistry)
    {
        $this->jobRegistry = $jobRegistry;
        $this->filesystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'createWorkingDirectory',
            EventInterface::AFTER_JOB_EXECUTION => 'deleteWorkingDirectory',
        ];
    }

    /**
     * Create the working directory if necessary.
     *
     * The pathname is placed in the JobExecutionContext via
     * the key {@link \Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface::CONTEXT_PARAMETER}
     *
     * @param JobExecutionEvent $event
     */
    public function createWorkingDirectory(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        if ($this->isJobWorkingDirectoryAware($jobExecution->getJobInstance())) {
            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('akeneo_batch_') . DIRECTORY_SEPARATOR;
            try {
                $this->filesystem->mkdir($path);
            } catch (IOException $e) {
                // this exception will be catched by {Job->execute()} and will set the batch as failed
                throw new RuntimeErrorException(
                    sprintf('Unable to create the working directory "%s".', $path),
                    $e->getCode(),
                    $e
                );
            }

            $jobExecution->getExecutionContext()->put(WorkingDirectoryAwareInterface::CONTEXT_PARAMETER, $path);
        }
    }

    /**
     * Delete the working directory if necessary.
     *
     * @param JobExecutionEvent $event
     */
    public function deleteWorkingDirectory(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        if ($this->isJobWorkingDirectoryAware($jobExecution->getJobInstance())) {
            $path = $jobExecution->getExecutionContext()->get(WorkingDirectoryAwareInterface::CONTEXT_PARAMETER);
            if (null !== $path && $this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }
    }

    /**
     * Determine if a job is working directory aware.
     *
     * @param JobInstance $jobInstance
     *
     * @return bool
     */
    private function isJobWorkingDirectoryAware(JobInstance $jobInstance)
    {
        try {
            $job = $this->jobRegistry->get($jobInstance->getCode());
        } catch (UndefinedJobException $e) {
            return false;
        }

        foreach ($job->getSteps() as $step) {
            if ($step instanceof WorkingDirectoryAwareInterface) {
                return true;
            }

            if ($step instanceof ItemStep && (
                    $step->getReader() instanceof WorkingDirectoryAwareInterface ||
                    $step->getProcessor() instanceof WorkingDirectoryAwareInterface ||
                    $step->getWriter() instanceof WorkingDirectoryAwareInterface
                )) {
                return true;
            }
        }

        return false;
    }
}
