<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\MakeJsonRequestTo;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadedFilesFormRequestTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Controllers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Controllers\UploadedFilesFormRequestTest
 */
class UploadedFilesFormRequestTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequestTo;

    public function testCanValidateUploadedFiles()
    {
        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.uploaded_file', method: 'POST', payload: [
            'files' => [
                'file' => new UploadedFile(__FILE__, 'uploaded_file.txt', 'text/plain'),
            ]
        ]);

        $this->assertEquals(['file' => 'UploadedFilesFormRequestTest.php'], $ret);
    }
}
