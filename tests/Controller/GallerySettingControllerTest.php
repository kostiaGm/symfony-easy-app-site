<?php

namespace App\Test\Controller;

use App\Entity\GallerySetting;
use App\Repository\GallerySettingRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GallerySettingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private GallerySettingRepository $repository;
    private string $path = '/gallery/setting/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(GallerySetting::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('GallerySetting index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'gallery_setting[name]' => 'Testing',
            'gallery_setting[width]' => 'Testing',
            'gallery_setting[height]' => 'Testing',
            'gallery_setting[path]' => 'Testing',
            'gallery_setting[isDefault]' => 'Testing',
            'gallery_setting[gallery]' => 'Testing',
        ]);

        self::assertResponseRedirects('/gallery/setting/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new GallerySetting();
        $fixture->setName('My Title');
        $fixture->setWidth('My Title');
        $fixture->setHeight('My Title');
        $fixture->setPath('My Title');
        $fixture->setIsDefault('My Title');
        $fixture->setGallery('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('GallerySetting');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new GallerySetting();
        $fixture->setName('My Title');
        $fixture->setWidth('My Title');
        $fixture->setHeight('My Title');
        $fixture->setPath('My Title');
        $fixture->setIsDefault('My Title');
        $fixture->setGallery('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'gallery_setting[name]' => 'Something New',
            'gallery_setting[width]' => 'Something New',
            'gallery_setting[height]' => 'Something New',
            'gallery_setting[path]' => 'Something New',
            'gallery_setting[isDefault]' => 'Something New',
            'gallery_setting[gallery]' => 'Something New',
        ]);

        self::assertResponseRedirects('/gallery/setting/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getWidth());
        self::assertSame('Something New', $fixture[0]->getHeight());
        self::assertSame('Something New', $fixture[0]->getPath());
        self::assertSame('Something New', $fixture[0]->getIsDefault());
        self::assertSame('Something New', $fixture[0]->getGallery());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new GallerySetting();
        $fixture->setName('My Title');
        $fixture->setWidth('My Title');
        $fixture->setHeight('My Title');
        $fixture->setPath('My Title');
        $fixture->setIsDefault('My Title');
        $fixture->setGallery('My Title');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/gallery/setting/');
    }
}
