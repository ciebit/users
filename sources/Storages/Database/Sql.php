<?php
declare(strict_types=1);
namespace Ciebit\Users\Storages\Database;

use Ciebit\Users\User;
use Ciebit\Users\Collection;
use Ciebit\Users\Status;
use Ciebit\Users\Storages\Storage;
use Ciebit\Users\Storages\Database\SqlHelper;
use Exception;
use PDO;

use function array_column;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function explode;
use function implode;
use function is_aray;
use function intval;

class Sql implements Database
{
    public const FIELD_EMAIL = 'email';
    public const FIELD_ID = 'id';
    public const FIELD_PASSWORD = 'password';
    public const FIELD_STATUS = 'status';
    public const FIELD_USERNAME = 'username';

    /** @var int */
    static private $counterKey = 0;

    /** @var PDO */
    private $pdo;

    /** @var SqlHelper */
    private $sqlHelper;

    /** @var string */
    private $table;

    /** @var int */
    private $totalItemsLastQuery;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sqlHelper = new SqlHelper;
        $this->table = 'cb_users';
        $this->totalItemsLastQuery = 0;
    }

    private function addFilter(string $fieldName, int $type, string $operator, ...$value): self
    {
        $field = "`{$this->table}`.`{$fieldName}`";
        $this->sqlHelper->addFilterBy($field, $type, $operator, ...$value);
        return $this;
    }

    public function addFilterByEmail(string $operator = '=', string ...$email): Storage
    {
        $this->addFilter(self::FIELD_EMAIL, PDO::PARAM_STR, $operator, ...$email);
        return $this;
    }

    public function addFilterById(string $operator = '=', int ...$id): Storage
    {
        $this->addFilter(self::FIELD_ID, PDO::PARAM_STR, $operator, ...$id);
        return $this;
    }

    public function addFilterByStatus(string $operator = '=', Status ...$status): Storage
    {
        $this->addFilter(self::FIELD_STATUS, PDO::PARAM_INT, $operator, ...$status);
        return $this;
    }

    public function addFilterByUsername(string $operator = '=', string ...$username): Storage
    {
        $this->addFilter(self::FIELD_USERNAME, PDO::PARAM_STR, $operator, ...$username);
        return $this;
    }

    public function addOrderBy(string $column, string $order = "ASC"): Database
    {
        $this->sqlHelper->addOrderBy("`{$this->table}`.`{$column}`", $order);
        return $this;
    }

    public function createUser(array $data): User
    {
        return (new User(
            $data['username'] ?? '',
            new Status((int) ($data['status'] ?? Status::INACTIVE))
        ))
        ->setId($data['id'] ?? '')
        ->setEmail($data['email'] ?? '')
        ->setPassword($data['password'] ?? '');
    }

    public function destroy(User $user): Storage
    {
        $fieldId = self::FIELD_ID;

        $statement = $this->pdo->prepare("
            DELETE FROM {$this->table} WHERE `{$fieldId}` = :id;
        ");

        $statement->bindValue(':id', (int) $user->getId(), PDO::PARAM_INT);
        $statement->execute();

        return $this;
    }

    private function getFields(): string
    {
        $table = $this->table;
        $fields = [
            self::FIELD_EMAIL,
            self::FIELD_ID,
            self::FIELD_STATUS,
            self::FIELD_USERNAME,
            self::FIELD_PASSWORD
        ];

        $fields = array_map(
            function($field) use ($table){
                return "`{$table}`.`{$field}`";
            },
            $fields
        );

        return implode(',', $fields);
    }

    public function getTotalRows(): int
    {
        return $this->totalItemsLastQuery;
    }

    /** @throws Exception */
    public function findOne(): ?User
    {
        $statement = $this->pdo->prepare("
            SELECT
            {$this->getFields()}
            FROM {$this->table}
            {$this->sqlHelper->generateSqlJoin()}
            WHERE {$this->sqlHelper->generateSqlFilters()}
            {$this->sqlHelper->generateSqlOrder()}
            LIMIT 1
        ");

        $this->sqlHelper->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.users.storages.database.get_error', 2);
        }

        $usersData = $statement->fetch(PDO::FETCH_ASSOC);

        if ($usersData == false) {
            return null;
        }

        $this->totalItemsLastQuery = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

        return $this->createUser($usersData);
    }

    /** @throws Exception */
    public function findAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table}
            {$this->sqlHelper->generateSqlJoin()}
            WHERE {$this->sqlHelper->generateSqlFilters()}
            {$this->sqlHelper->generateSqlOrder()}
            {$this->sqlHelper->generateSqlLimit()}
        ");

        $this->sqlHelper->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.users.storages.database.get_error', 2);
        }

        $collection = new Collection;
        $usersData = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usersData as $userData) {
            $user = $this->createUser($userData);
            $collection->add($user);
        }

        $this->totalItemsLastQuery = (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

        return $collection;
    }

    public function save(User $user): Storage
    {
        $field = self::FIELD_ID;
        $statement = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$field} = :id;"
        );

        $statement->bindValue(':id', (int) $user->getId(), PDO::PARAM_INT);

        $execute = $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        !$data
            ? $this->store($user)
            : $this->update($user);

        return $this;
    }

    public function setLimit(int $total): Storage
    {
        $this->sqlHelper->setLimit($total);
        return $this;
    }

    public function setOffset(int $offset): Storage
    {
        $this->sqlHelper->setOffset($offset);
        return $this;
    }

    public function setTable(string $name): Database
    {
        $this->table = $name;
        return $this;
    }

    public function store(User $user): Storage
    {
        $email = self::FIELD_EMAIL;
        $password = self::FIELD_PASSWORD;
        $status = self::FIELD_STATUS;
        $username = self::FIELD_USERNAME;

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} (`{$username}`, `{$password}`, `{$email}`, `{$status}`)
            VALUES (:username, :password, :email, :status)"
        );

        $statement->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $statement->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $statement->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':status', $user->getStatus()->getValue(), PDO::PARAM_INT);

        $statement->execute();

        $user->setId($this->pdo->lastInsertId());

        return $this;
    }

    public function update(User $user): Storage
    {
        $email = self::FIELD_EMAIL;
        $id = self::FIELD_ID;
        $password = self::FIELD_PASSWORD;
        $status = self::FIELD_STATUS;
        $username = self::FIELD_USERNAME;

        $statement = $this->pdo->prepare(
            "UPDATE {$this->table} SET
            {$username} = :username,
            {$password} = :password,
            {$email} = :email,
            {$status} = :status
            WHERE {$id} = :id;"
        );

        $statement->bindValue(':id', (int) $user->getId(), PDO::PARAM_INT);
        $statement->bindValue(':username', $user->getUsername(), PDO::PARAM_STR);
        $statement->bindValue(':password', $user->getPassword(), PDO::PARAM_STR);
        $statement->bindValue(':email', $user->getEmail(), PDO::PARAM_STR);
        $statement->bindValue(':status', $user->getStatus()->getValue(), PDO::PARAM_INT);

        $statement->execute();

        return $this;
    }
}
