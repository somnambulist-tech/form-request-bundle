<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\AuthenticatedFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\MimesFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\ProductFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\SearchUsersFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UploadedFileFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UserFormRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ViewController extends AbstractController
{

    public function simpleFormAction(UserFormRequest $form)
    {
        return new JsonResponse($form->data()->all());
    }

    public function nestedFormAction(ProductFormRequest $form)
    {
        return new JsonResponse($form->data()->all());
    }

    public function authenticatedAction(AuthenticatedFormRequest $form)
    {
        return new JsonResponse($form->data()->all());
    }

    public function uploadedFileAction(UploadedFileFormRequest $form)
    {
        return new JsonResponse(['file' => $form->data()->get('file')->getFilename()]);
    }

    public function mimesAction(MimesFormRequest $form)
    {
        return new JsonResponse(['file' => $form->data()->get('file')->getFilename()]);
    }

    public function ignoreAction(SearchUsersFormRequest $form)
    {
        return new JsonResponse(
            $form->data()->prune()->all(),
            200,
            ['X-Page' => $form->get('page'), 'X-PerPage' => $form->get('per_page')]
        );
    }
}
