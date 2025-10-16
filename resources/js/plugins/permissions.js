export default {
    install(app) {
        // ⭐ Verificar se o usuário tem uma permissão específica
        app.config.globalProperties.$can = (permission) => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const permissions = user.permissions || [];
            return permissions.includes(permission);
        };

        // ⭐ Verificar se o usuário tem alguma das permissões
        app.config.globalProperties.$canAny = (...permissions) => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const userPermissions = user.permissions || [];
            return permissions.some(p => userPermissions.includes(p));
        };

        // ⭐ Verificar se o usuário tem todas as permissões
        app.config.globalProperties.$canAll = (...permissions) => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const userPermissions = user.permissions || [];
            return permissions.every(p => userPermissions.includes(p));
        };

        // ⭐ Verificar se o usuário tem um perfil específico
        app.config.globalProperties.$hasRole = (role) => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const roles = user.roles || [];
            return roles.includes(role);
        };
    }
};
