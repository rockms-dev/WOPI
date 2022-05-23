<?php

namespace MS\Wopi\Contracts\Concerns;

interface Deleteable
{
    /**
     * Delete the document.
     */
    public function delete(): void;

    /**
     * Indicates that the host supports the DeleteFile operation.
     */
    public function supportDelete(): bool;
}
