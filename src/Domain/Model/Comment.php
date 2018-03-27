<?php

declare(strict_types=1);

namespace Sylius\OrderCommentsPlugin\Domain\Model;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SimpleBus\Message\Recorder\ContainsRecordedMessages;
use SimpleBus\Message\Recorder\PrivateMessageRecorderCapabilities;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\OrderCommentsPlugin\Domain\Event\FileAttached;
use Sylius\OrderCommentsPlugin\Domain\Event\OrderCommented;

final class Comment implements ResourceInterface, ContainsRecordedMessages
{
    use PrivateMessageRecorderCapabilities;

    /** @var UuidInterface */
    private $id;

    /** @var OrderInterface */
    private $order;

    /** @var Email */
    private $authorEmail;

    /** @var string */
    private $message;

    /** @var bool */
    private $readByUser = false;

    /** @var bool */
    private $readByAdmin = false;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var AttachedFile */
    private $attachedFile;

    public function __construct(OrderInterface $order, string $authorEmail, string $message)
    {
        if (null == $message) {
            throw new \DomainException('OrderComment cannot be created with empty message');
        }

        $this->id = Uuid::uuid4();
        $this->authorEmail = Email::fromString($authorEmail);
        $this->order = $order;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();

        $this->record(
            OrderCommented::occur(
                $this->id,
                $this->order,
                $this->authorEmail,
                $this->message,
                $this->createdAt
            )
        );
    }

    public function attachFile(string $path)
    {
        $this->attachedFile = AttachedFile::create($path);

        $this->record(
            FileAttached::occur($this->attachedFile->path())
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function order(): OrderInterface
    {
        return $this->order;
    }

    public function authorEmail(): Email
    {
        return $this->authorEmail;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function createdAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function attachedFile(): ?AttachedFile
    {
        return $this->attachedFile;
    }
    /**
     * @return bool
     */
    public function isReadByUser(): bool
    {
        return $this->readByUser;
    }

    /**
     * @param bool $readByUser
     */
    public function setReadByUser(bool $readByUser): void
    {
        $this->readByUser = $readByUser;
    }

    /**
     * @return bool
     */
    public function isReadByAdmin(): bool
    {
        return $this->readByAdmin;
    }

    /**
     * @param bool $readByAdmin
     */
    public function setReadByAdmin(bool $readByAdmin): void
    {
        $this->readByAdmin = $readByAdmin;
    }
}
