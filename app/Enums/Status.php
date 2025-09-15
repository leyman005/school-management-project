<?php

namespace App\Enums;

class Status
{
	// Define possible statuses
	public const ACTIVE = 'active';
	public const INACTIVE = 'inactive';
	public const SUSPENDED = 'suspended';
	public const GRADUATED = 'graduated';
	public const EXPELLED = 'expelled';

	public static function all(): array
	{
		return [
			self::ACTIVE,
			self::INACTIVE,
			self::SUSPENDED,
			self::GRADUATED,
			self::EXPELLED,
		];
	}
}
