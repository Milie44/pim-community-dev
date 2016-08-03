<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Checks if the logged in user has the permission to edit Content tab properties of an export job profile
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentSubscriber implements EventSubscriberInterface
{
    const CONTENT_TAB_EDIT_ACL_ID = 'pim_importexport_export_profile_content_edit';

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
        $parameters = $jobInstance->getRawParameters();

        $filtersConfiguration = $parameters['filters'];

        if ($this->securityFacade->isGranted(self::CONTENT_TAB_EDIT_ACL_ID)) {
            $filtersConfiguration = json_decode($data['parameters']['filters'], true);
        }

        $data['parameters']['filters'] = $filtersConfiguration;

        $event->setData($data);
    }
}
