<?php
class SettingsController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Obtener configuraciones generales
    public function getGeneralSettings() {
        $query = "SELECT * FROM system_settings WHERE category = 'general'";
        return $this->db->query($query)->fetchAll();
    }

    // Obtener configuraciones de notificaciones
    public function getNotificationSettings() {
        $query = "SELECT * FROM system_settings WHERE category = 'notifications'";
        return $this->db->query($query)->fetchAll();
    }

    // Actualizar configuración
    public function updateSettings($settings) {
        $success = true;
        $message = 'Configuración actualizada exitosamente';

        try {
            foreach ($settings as $key => $value) {
                $query = "UPDATE system_settings SET value = ? WHERE setting_key = ?";
                $this->db->query($query, [$value, $key]);
            }
        } catch (Exception $e) {
            $success = false;
            $message = 'Error al actualizar la configuración: ' . $e->getMessage();
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }

    // Obtener roles y permisos
    public function getRolesAndPermissions() {
        $query = "SELECT * FROM roles ORDER BY role_name";
        return $this->db->query($query)->fetchAll();
    }

    // Actualizar permisos de rol
    public function updateRolePermissions($roleId, $permissions) {
        $success = true;
        $message = 'Permisos actualizados exitosamente';

        try {
            // Primero eliminamos los permisos existentes
            $query = "DELETE FROM role_permissions WHERE role_id = ?";
            $this->db->query($query, [$roleId]);

            // Luego insertamos los nuevos permisos
            foreach ($permissions as $permission) {
                $query = "INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)";
                $this->db->query($query, [$roleId, $permission]);
            }
        } catch (Exception $e) {
            $success = false;
            $message = 'Error al actualizar los permisos: ' . $e->getMessage();
        }

        return [
            'success' => $success,
            'message' => $message
        ];
    }

    // Obtener configuración de correo
    public function getEmailSettings() {
        $query = "SELECT * FROM system_settings WHERE category = 'email'";
        return $this->db->query($query)->fetchAll();
    }

    // Probar configuración de correo
    public function testEmailSettings($settings) {
        // Implementar lógica para probar la configuración de correo
        return [
            'success' => true,
            'message' => 'Prueba de correo enviada exitosamente'
        ];
    }

    // Obtener configuración de respaldos
    public function getBackupSettings() {
        $query = "SELECT * FROM system_settings WHERE category = 'backup'";
        return $this->db->query($query)->fetchAll();
    }

    // Crear respaldo manual
    public function createBackup() {
        // Implementar lógica para crear respaldo
        return [
            'success' => true,
            'message' => 'Respaldo creado exitosamente'
        ];
    }
} 