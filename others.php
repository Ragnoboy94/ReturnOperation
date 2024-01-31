<?php

namespace NW\WebService\References\Operations\Notification;


class Contractor
{
    const TYPE_CUSTOMER = 0;
    protected int $id;
    protected int $type;
    protected string $name;

    /**
     * Contractor.
     * @param int $id Идентификатор исполнителя
     * @param int $type Тип исполнителя
     * @param string $name Имя исполнителя
     */
    public function __construct(int $id, int $type, string $name)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Получаем исполнителя по ID.
     * @param int $id Идентификатор исполнителя
     * @return self
     */
    public static function getById(int $id): self
    {
        return new self($id, self::TYPE_CUSTOMER, "Example Name");
    }

    /**
     * Получаем полное имя исполнителя.
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->id;
    }
}

class Seller extends Contractor
{
    //
}

class Employee extends Contractor
{
    //
}


class Status
{
    protected int $id;
    protected string $name;

    /**
     * Получаем имя статуса по ID.
     * @param int $id Идентификатор статуса
     * @return string
     */
    public static function getName(int $id): string
    {
        $statuses = [
            0 => 'Completed',
            1 => 'Pending',
            2 => 'Rejected',
        ];

        return $statuses[$id] ?? 'Неизвестный статус';
    }
}


abstract class ReferencesOperation
{
    /**
     * @return array
     */
    abstract public function doOperation(): array;

    /**
     * Получаем параметр запроса.
     * @param string $pName Название параметра
     * @return mixed
     */
    public function getRequest(string $pName)
    {
        return filter_input(INPUT_REQUEST, $pName, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}

/**
 * Получаем email
 * @return string
 */
function getResellerEmailFrom(): string
{
    // TODO: Здесь можно и нужно реализовать нормальное получение емейла, но данных мало
    return 'contractor@example.com';
}

/**
 * Получаем email по событию.
 * @param int $resellerId Идентификатор
 * @param string $event Событие
 * @return array
 */
function getEmailsByPermit(int $resellerId, string $event): array
{
    return ['someemail@example.com', 'someemail2@example.com'];
}

class NotificationEvents
{
    const CHANGE_RETURN_STATUS = 'changeReturnStatus';
    const NEW_RETURN_STATUS = 'newReturnStatus';
}


