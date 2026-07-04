<?php

namespace App\Models;

use App\Core\Model;

/**
 * User account data access for authentication workflows.
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Create a new user account with a hashed password.
     */
    public function create(array $data): int
    {
        $statement = $this->query(
            'INSERT INTO users (fullname, username, email, phone, country, password, account_status)
             VALUES (:fullname, :username, :email, :phone, :country, :password, :account_status)',
            [
                'fullname' => $data['fullname'],
                'username' => $data['username'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'country' => $data['country'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'account_status' => 'pending',
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->query('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Find a user by username.
     */
    public function findByUsername(string $username): ?array
    {
        $statement = $this->query('SELECT * FROM users WHERE username = :username LIMIT 1', ['username' => $username]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Find a user by email while excluding a known account ID.
     */
    public function findByEmailExcept(string $email, int $userId): ?array
    {
        $statement = $this->query(
            'SELECT * FROM users WHERE email = :email AND id <> :id LIMIT 1',
            ['email' => $email, 'id' => $userId]
        );
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Find a user by username while excluding a known account ID.
     */
    public function findByUsernameExcept(string $username, int $userId): ?array
    {
        $statement = $this->query(
            'SELECT * FROM users WHERE username = :username AND id <> :id LIMIT 1',
            ['username' => $username, 'id' => $userId]
        );
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Find a user by email address or username.
     */
    public function findByLogin(string $login): ?array
    {
        $statement = $this->query(
            'SELECT * FROM users WHERE email = :email_login OR username = :username_login LIMIT 1',
            [
                'email_login' => $login,
                'username_login' => $login,
            ]
        );
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Update a user's password.
     */
    public function updatePassword(int $userId, string $password): void
    {
        $this->query(
            'UPDATE users SET password = :password WHERE id = :id',
            [
                'id' => $userId,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );
    }

    /**
     * Update profile fields for a user.
     */
    public function updateProfile(int $userId, array $data): void
    {
        $fields = [];
        $params = ['id' => $userId];

        if (isset($data['fullname'])) {
            $fields[] = 'fullname = :fullname';
            $params['fullname'] = $data['fullname'];
        }

        if (isset($data['phone'])) {
            $fields[] = 'phone = :phone';
            $params['phone'] = $data['phone'];
        }

        if (isset($data['country'])) {
            $fields[] = 'country = :country';
            $params['country'] = $data['country'];
        }

        if (isset($data['avatar'])) {
            $fields[] = 'avatar = :avatar';
            $params['avatar'] = $data['avatar'];
        }

        if (isset($data['bio'])) {
            $fields[] = 'bio = :bio';
            $params['bio'] = $data['bio'];
        }

        if ($fields === []) {
            return;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $this->query($sql, $params);
    }

    /**
     * Update profile and account fields that administrators are allowed to manage.
     */
    public function updateByAdmin(int $userId, array $data): void
    {
        $allowed = [
            'fullname',
            'username',
            'email',
            'phone',
            'country',
            'bio',
            'role',
            'account_status',
            'email_verified',
        ];
        $fields = [];
        $params = ['id' => $userId];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $fields[] = $field . ' = :' . $field;
            $params[$field] = $data[$field];
        }

        if ($fields === []) {
            return;
        }

        $this->query('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id', $params);
    }

    /**
     * Return all user ids for broadcast notifications.
     */
    public function allIds(): array
    {
        $statement = $this->query('SELECT id FROM users');

        return $statement->fetchAll();
    }
}
