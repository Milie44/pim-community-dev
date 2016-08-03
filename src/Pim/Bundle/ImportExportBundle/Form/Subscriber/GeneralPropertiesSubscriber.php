<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Checks if the logged in user has the permission to edit general properties tab of an export job profile
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GeneralPropertiesSubscriber implements EventSubscriberInterface
{
    const GENERAL_PROPERTY_TAB_ACL_ID = 'pim_importexport_export_profile_property_edit';

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        SecurityFacade $securityFacade
    ) {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Injects missing job config that we retrieve from jobInstance configuration in order to make the form pass
     * the form validation.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $jobInstance = $form->getData();

        if (null !== $jobInstance && null === $jobInstance->getId()) {
            return;
        }

        $data = $event->getData();
        if (!isset($data['parameters'])) {
            return;
        }

        $parameters = array_filter($jobInstance->getRawParameters(), function ($parameter) {
            return 'filters' !== $parameter;
        }, ARRAY_FILTER_USE_KEY);

        $jobParameters = array_replace_recursive($data['parameters'], $parameters);

        if ($this->securityFacade->isGranted(self::GENERAL_PROPERTY_TAB_ACL_ID)) {
            $jobParameters = array_replace_recursive($jobParameters, $data['parameters']);
        }

        $data['parameters'] = $jobParameters;
        if (!isset($data['label'])) {
            $data['label'] = $jobInstance->getLabel();
        }

        $event->setData($data);
    }
}
