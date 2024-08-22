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
            'createdAt' => '2024-08-22T16:54:12.469015+0200'
        ];

        $model = new Task();
        $form = $this->factory->create(TaskType::class, $model);

        $expected = new Task();
        $expected->setTitle('testtitle');
        $expected->setContent("Ceci est un exemple du contenu d'une tache");
        $expected->setCreatedAt('2024-08-22T16:54:12.469015+0200');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;
    }
}