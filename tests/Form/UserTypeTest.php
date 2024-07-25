<?php

namespace Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    public function testValidationFormulaireCreationUtilisateur()
    {
        $formData = [
            'username' => 'testuser',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'email' => 'test@example.com',
            'roles' => ['ROLE_ADMIN'],
        ];

        $model = new User();
        $form = $this->factory->create(UserType::class, $model);

        $expected = new User();
        $expected->setUsername('testuser');
        $expected->setPassword('password123');
        $expected->setEmail('test@example.com');
        $expected->setRoles(['ROLE_ADMIN']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}