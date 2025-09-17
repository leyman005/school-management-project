<?php

namespace App\Enums;

enum Role: string
{
	// Define possible roles
	case ADMIN = 'admin';
	case PERSONEL = 'personel';
	case STUDENT = 'student';

	public static function all(): array
	{
		return [
			self::ADMIN,
			self::PERSONEL,
			self::STUDENT,
		];
	}
}
