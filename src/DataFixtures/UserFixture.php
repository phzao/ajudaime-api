<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserFixture
 * @package App\DataFixtures
 */
class UserFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $data = [
            "email"    => "eu@eu.com",
            "password" => "12345"
        ];
        $user->setAttributes($data);
        $manager->persist($user);
        $manager->flush();
    }
}
