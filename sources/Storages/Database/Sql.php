<?php
declare(strict_types=1);
namespace Ciebit\Users\Storages\Database;

use Ciebit\Users\User;
use Ciebit\Users\Collection;
use Ciebit\Users\Status;
use Ciebit\Users\Storages\Storage;
use Exception;
use PDO;

use function array_column;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function explode;
use function is_aray;
use function intval;

class Sql extends SqlFilters implements Database
{
    static private $counterKey = 0;
    private $pdo; #PDO
    private $tableGet; #string
    private $tableSave; #string

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->tableGet = 'cb_users';
        $this->tableSave = 'cb_users';
    }

    public function addFilterById(int $id, string $operator = '='): Storage
    {
        $key = 'id';
        $sql = "`users`.`id` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_STR, $id);
        return $this;
    }

    public function addFilterByIds(string $operator, int ...$ids): Storage
    {
         $keyPrefix = 'id';
         $keys = [];
         $operator = $operator == '!=' ? 'NOT IN' : 'IN';
         foreach ($ids as $id) {
             $key = $keyPrefix . self::$counterKey++;
             $this->addBind($key, PDO::PARAM_STR, $id);
             $keys[] = $key;
         }
         $keysStr = implode(', :', $keys);
         $this->addSqlFilter("`users`.`id` {$operator} (:{$keysStr})");
         return $this;
    }

    public function addFilterByUsername(string $username, string $operator = '='): Storage
    {
        $key = 'username';
        $sql = "`users`.`username` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_STR, $username);
        return $this;
    }

    public function addFilterByEmail(string $email, string $operator = '='): Storage
    {
        $key = 'email';
        $sql = "`users`.`email` $operator :{$key}";
        $this->addfilter($key, $sql, PDO::PARAM_STR, $email);
        return $this;
    }

    public function addFilterByStatus(Status $status, string $operator = '='): Storage
    {
        $key = 'status';
        $sql = "`users`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        return $this;
    }

    public function get(): ?User
    {
        $statement = $this->pdo->prepare("
            SELECT
            {$this->getFields()}
            FROM {$this->tableGet} as `users`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            LIMIT 1
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.users.storages.database.get_error', 2);
        }
        $usersData = $statement->fetch(PDO::FETCH_ASSOC);
        if ($usersData == false) {
            return null;
        }

        return $this->createUser($usersData);
    }

    public function createUser(array $data): User
    {
        return (new User(
            $data['username'] ?? '',
            new Status((int) $data['status'] ?? new Status(5))
        ))
        ->setId($data['id'] ?? '')
        ->setEmail($data['email'] ?? '')
        ->setPassword($data['password'] ?? '');
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->tableGet} as `users`
            {$this->generateSqlJoin()}
            WHERE {$this->generateSqlFilters()}
            {$this->generateOrder()}
            {$this->generateSqlLimit()}
        ");
        $this->bind($statement);
        if ($statement->execute() === false) {
            throw new Exception('ciebit.users.storages.database.get_error', 2);
        }
        $collection = new Collection;
        $usersData = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($usersData as $userData) {
            $user = $this->createUser($userData);
            $collection->add($user);
        }
        return $collection;
    }

    private function getFields(): string
    {
        return '
            `users`.`id`,
            `users`.`username`,
            `users`.`password`,
            `users`.`email`,
            `users`.`status`
        ';
    }

    public function getTotalRows(): int
    {
        return (int) $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStartingLine(int $lineInit): Storage
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTableGet(string $name): Database
    {
        $this->tableGet = $name;
        return $this;
    }

    public function setTableSave(string $name): Database
    {
        $this->tableSave = $name;
        return $this;
    }

    public function setTotalLines(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }
}
