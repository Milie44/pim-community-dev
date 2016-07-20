<?php

namespace Akeneo\Component\Batch\Step;

/**
 * Determine if a step (or an element of the item step) needs a working directory to perform its work.
 * If it's the case, then:
 *   - the working directory is created before the execution of the job
 *     (event Akeneo\Component\Batch\Event\EventInterface\BEFORE_JOB_EXECUTION)
 *   - the working directory is deleted after the execution of the job
 *     (event Akeneo\Component\Batch\Event\EventInterface\AFTER_JOB_EXECUTION)
 *
 * An implementation of the creation of the working directory is performed directly in the BatchBundle
 * via the event subscriber {@link Akeneo\Bundle\BatchBundle\EventListener\WorkingDirectorySubscriber}:
 *   - the working directory is created in the temporary filesystem
 *   - its pathname is placed in the JobExecutionContext via
 *     the key {@link \Akeneo\Component\Batch\Step\WorkingDirectoryAwareInterface::CONTEXT_PARAMETER}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface WorkingDirectoryAwareInterface
{
    const CONTEXT_PARAMETER = 'workingDirectory';

    public function getWorkingDirectory();
}
