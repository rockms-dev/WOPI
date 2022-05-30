<?php

namespace App\Services;

use MS\Wopi\Models\Document;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use MS\Wopi\Contracts\AbstractDocumentManager;
use MS\Wopi\Contracts\Concerns\Deleteable;
use MS\Wopi\Contracts\Concerns\HasHash;
use MS\Wopi\Contracts\Concerns\HasMetadata;
use MS\Wopi\Contracts\Concerns\HasUrlProprties;
use MS\Wopi\Contracts\Concerns\Renameable;
use MS\Wopi\Contracts\Concerns\StopRelayingOnBaseNameToGetFileExtension;
use MS\Wopi\Contracts\Concerns\InteractsWithUserInfo;
use MS\Wopi\Contracts\ConfigRepositoryInterface;
use App\Models\User;
use MS\Wopi\Contracts\Concerns\OverridePermissions;

class DBDocumentManager extends AbstractDocumentManager implements Deleteable, Renameable, HasHash, HasMetadata, StopRelayingOnBaseNameToGetFileExtension, InteractsWithUserInfo, OverridePermissions
{
    protected Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function userCanNotWriteRelative(): bool
    {
        // You can enable/disable this to complete the tests
        return false;
    }

    public function getUserInfo(): string
    {
        return (string) auth()->user()->info;
    }

    public function supportUserInfo(): bool
    {
        /** @var ConfigRepositoryInterface */
        $config = app(ConfigRepositoryInterface::class);

        return $config->supportUserInfo();
    }

    public static function putUserInfo(string $userInfo, ?string $documentId, ?string $accessToken): void
    {
        auth()->user()->update(['info' => $userInfo]);
    }

    public function sha256Hash(): string
    {
        return $this->document->hash;
    }

    public function lastModifiedTime(): string
    {
        return Carbon::parse($this->document->updated_at, 'UTC')->toIso8601String();
    }

    public function extension(): string
    {
        return ".".$this->document->extension;
    }

    public static function find(string $documentId): AbstractDocumentManager
    {
        $document =  Document::findorFail($documentId);
        return new static($document);
    }

    public static function findByName(string $documentname): AbstractDocumentManager
    {
        $document = Document::whereName($documentname)->firstOrFail();
        return new static($document);
    }

    public static function create(array $properties): AbstractDocumentManager
    {
        $hash = hash('sha256', base64_encode($properties['content']));

        $document = Document::create([
            'name' => $properties['basename'],
            'size' => $properties['size'],
            'path' => $properties['basename'],
            'lock' => '',
            'hash' => $hash,
            'version' => '1',
            'extension' => $properties['extension'],
            'user_id' => 1,
        ]);

        file_put_contents(Storage::disk('public')->path($properties['basename']), $properties['content']);

        return new static($document);
    }

    public function id(): string
    {
        return $this->document->id;
    }

    public function userFriendlyName(): string
    {
        $user = Auth::user();

        return is_null($user) ? 'Guest' : $user->first_name.' '.$user->last_name;
    }

    public function basename(): string
    {
        return $this->document->name;
    }

    public function owner(): string
    {
        return $this->document->user->id;
    }

    public function size(): int
    {
        return $this->document->size;
    }

    public function version(): string
    {
        return $this->document->version;
    }

    public function content(): string
    {
        return file_get_contents(Storage::disk('public')->path($this->document->path));
    }

    public function isLocked(): bool
    {
        return !empty($this->document->lock);
    }

    public function getLock(): string
    {
        return $this->document->lock;
    }

    public function put(string $content, array $editorsIds = []): void
    {
        // calculate content size and hash, be carefull with large contents!
        $size = strlen($content);
        $hash = hash('sha256', base64_encode($content));
        $newVersion = uniqid();

        file_put_contents(Storage::disk('public')->path($this->document->path), $content);
        $this->document->fill(['size' => $size, 'hash' => $hash, 'version' => $newVersion])->update();
    }

    public function deleteLock(): void
    {
        $this->document->fill(['lock' => ''])->update();
    }

    public function lock(string $lockId): void
    {
        $this->document->fill(['lock' => $lockId])->update();
    }

    public function delete(): void
    {
        Storage::disk('public')->delete($this->document->path);
        $this->document->delete();
    }

    public function rename(string $newName): void
    {
        $oldPath = $this->document->path;
        $this
            ->file
            ->fill(['name' => "{$newName}.{$this->document->extension}", 'path' => "{$newName}.{$this->file->extension}"])
            ->update();

        $newPath = $this->document->path;


        Storage::disk('public')->move($oldPath, $newPath);
    }

    public function canUserRename(): bool
    {
        return true;
    }
}