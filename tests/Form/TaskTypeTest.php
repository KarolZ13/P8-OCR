<?php

namespace Form;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

class TaskTypeTest extends TypeTestCase
{
    public function testValidationFormulaireCreationTache()
    {
        $formData = [
            'title' => 'testtitle',
            'content' => "Ceci est un exemple du contenu d'une tache",
        ];

        $model = new Task();
        $form = $this->factory->create(TaskType::class, $model);

        $expected = new Task();
        $expected->setTitle('testtitle');
        $expected->setContent("Ceci est un exemple du contenu d'une tache");

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);

        $this->assertNotNull($model->getCreatedAt());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}