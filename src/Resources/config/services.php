<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\AccessDeniedExceptionSubscriber;
use Somnambulist\Bundles\FormRequestBundle\EventSubscribers\FormValidationExceptionSubscriber;
use Somnambulist\Bundles\FormRequestBundle\Rules\MimeRule;
use Somnambulist\Bundles\FormRequestBundle\Rules\RequiredRule;
use Somnambulist\Bundles\FormRequestBundle\Rules\UploadedFileRule;
use Somnambulist\Bundles\FormRequestBundle\Services\FormRequestArgumentResolver;
use Somnambulist\Components\Validation\Contracts\MimeTypeGuesser as MimeTypeGuesserContract;
use Somnambulist\Components\Validation\Factory;
use Somnambulist\Components\Validation\MimeTypeGuesser;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private()
        ->autowire()
        ->autoconfigure();

    $services->set(Factory::class, Factory::class)
        ->public();

    $services->set(MimeTypeGuesser::class, MimeTypeGuesser::class)
        ->public();

    $services->alias(MimeTypeGuesser::class, MimeTypeGuesserContract::class);

    $services->set(FormRequestArgumentResolver::class, FormRequestArgumentResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 105]);

    $services->set(RequiredRule::class, RequiredRule::class)
        ->tag('somnambulist.form_request_bundle.rule', ['rule_name' => 'required']);

    $services->set(UploadedFileRule::class, UploadedFileRule::class)
        ->tag('somnambulist.form_request_bundle.rule', ['rule_name' => 'uploaded_file']);

    $services->set(MimeRule::class, MimeRule::class)
        ->tag('somnambulist.form_request_bundle.rule', ['rule_name' => 'mimes']);

    $services->set(AccessDeniedExceptionSubscriber::class, AccessDeniedExceptionSubscriber::class);

    $services->set(FormValidationExceptionSubscriber::class, FormValidationExceptionSubscriber::class);
};
