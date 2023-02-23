<?php

namespace App\Foundation\Interfaces\Logger;

use App\Data\Models\User;
use Illuminate\Database\Eloquent\Model;

interface EntityActionLoggerInterface
{
    /**
     * If lazy save enabled
     *
     * Means, if use was looking like `EntityActionLogger::entity()`
     */
    public const LAZY_SAVE = true;

    /**
     * Method to define the entity
     *
     * @param  Model  $model
     * @return static
     */
    public function entity(Model $model): self;

    public function by(User $user): self;


    public function addContext(array $context): self;

    /**
     * Action to store logs right away
     *
     * @return bool
     */
    public function storeLog(): bool;

    /**
     * Action is saying to store logs with __destruct
     *
     * @return bool
     */
    public function mustStore(): bool;
}
