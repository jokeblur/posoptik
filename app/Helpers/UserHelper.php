<?php

namespace App\Helpers;

class UserHelper
{
    /**
     * Get user initials from name
     * 
     * @param string $name
     * @return string
     */
    public static function getInitials($name)
    {
        if (empty($name)) {
            return 'U';
        }

        $words = explode(' ', trim($name));
        $initials = '';

        if (count($words) >= 2) {
            // Get first letter of first name and first letter of last name
            $initials = strtoupper(substr($words[0], 0, 1)) . strtoupper(substr($words[count($words) - 1], 0, 1));
        } else {
            // If only one word, get first two letters
            $initials = strtoupper(substr($name, 0, 2));
        }

        return $initials;
    }

    /**
     * Get user role display name
     * 
     * @param string $role
     * @return string
     */
    public static function getRoleDisplayName($role)
    {
        $roleNames = [
            'super_admin' => 'Super Admin',
            'admin' => 'Administrator',
            'kasir' => 'Kasir',
            'user' => 'User'
        ];

        return $roleNames[$role] ?? ucfirst($role);
    }

    /**
     * Get user status color based on role
     * 
     * @param string $role
     * @return string
     */
    public static function getRoleColor($role)
    {
        $roleColors = [
            'super_admin' => '#dc3545', // Red
            'admin' => '#fd7e14',       // Orange
            'kasir' => '#28a745',       // Green
            'user' => '#6c757d'         // Gray
        ];

        return $roleColors[$role] ?? '#6c757d';
    }
} 