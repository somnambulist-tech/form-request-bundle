<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\UserProvider
 */
class UserProvider implements UserProviderInterface
{
    public function loadUserByUsername(string $username)
    {
        $users = [
            'tester' => new User('tester', 'password', ['ROLE_USER']),
            'admin'  => new User('admin', 'password', ['ROLE_USER', 'ROLE_ADMIN']),
        ];

        return $users[$username] ?? throw new UsernameNotFoundException();
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass(string $class)
    {
        return is_a($class, User::class, true);
    }
}
