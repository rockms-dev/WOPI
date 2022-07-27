<?php

namespace App\Services;

use MS\Wopi\Models\File;
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
    protected File $file;

    public function __construct(File $file)
    {
        $this->file = $file;
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
        return (string) auth()->user()?->info;
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
        return $this->file->hash;
    }

    public function lastModifiedTime(): string
    {
        return Carbon::parse($this->file->updated_at, 'UTC')->toIso8601String();
    }

    public function extension(): string
    {
        return ".".$this->file->extension;
    }

    public static function find(string $documentId): AbstractDocumentManager
    {
        $document =  File::findorFail($documentId);
        return new static($document);
    }

    public static function findByName(string $documentname): AbstractDocumentManager
    {
        $document = File::whereName($documentname)->firstOrFail();
        return new static($document);
    }

    public static function create(array $properties): AbstractDocumentManager
    {
        $hash = hash('sha256', base64_encode($properties['content']));

        $document = File::create([
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
        return $this->file->id;
    }

    public function userFriendlyName(): string
    {
        $user = Auth::user();

        return is_null($user) ? 'Guest' : $user->first_name.' '.$user->last_name;
    }

    public function basename(): string
    {
        return $this->file->name;
    }

    public function storagePath(): ?string
    {
        return $this->file->storage_path;
    }

    public function url(): ?string
    {
        return $this->file->url;
    }

    public function path(): ?string
    {
        return $this->file->path;
    }

    public function setFilePath($path): void
    {
        $this->file->path = $path;
        $this->file->save();
    }

    public function owner(): string
    {
        return $this->file->user->id;
    }

    public function size(): int
    {
        return $this->file->size;
    }

    public function version(): string
    {
        return $this->file->version;
    }

    public function content(): string
    {
        return file_get_contents(Storage::disk('public')->path($this->file->path));
    }

    public function isLocked(): bool
    {
        return !empty($this->file->lock);
    }

    public function getLock(): string
    {
        return $this->file->lock;
    }

    public function put(string $content, array $editorsIds = []): void
    {
        // calculate content size and hash, be carefull with large contents!
        $size = strlen($content);
        $hash = hash('sha256', base64_encode($content));
        $newVersion = time();

        file_put_contents(Storage::disk('public')->path($this->file->path), $content);
        $this->file->fill(['size' => $size, 'version' => $newVersion])->update();
    }

    public function deleteLock(): void
    {
        $this->file->fill(['lock' => ''])->update();
    }

    public function lock(string $lockId): void
    {
        $this->file->fill(['lock' => $lockId])->update();
    }

    public function delete(): void
    {
        Storage::disk('public')->delete($this->file->path);
        $this->file->delete();
    }

    public function rename(string $newName): void
    {
        $oldPath = $this->file->path;
        $this
            ->file
            ->fill(['name' => "{$newName}.{$this->file->extension}", 'path' => "{$newName}.{$this->file->extension}"])
            ->update();

        $newPath = $this->file->path;


        Storage::disk('public')->move($oldPath, $newPath);
    }

    public function canUserRename(): bool
    {
        return true;
    }
}