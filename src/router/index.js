import { createRouter, createWebHistory } from 'vue-router';
import { getCurrentInstance } from 'vue';
import { useUserStore } from '@/stores/user';

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: '',
			component: () => import('@/views/index.vue'),
			redirect: '/dashboard',
			children: [
				{
					path: '/dashboard',
					name: 'HomePage',
					meta: {
						pageTitle: 'Home Page',
					},
					component: () => import('@/views/pages/HomePage.vue'),
				},
				{
					path: 'users',
					name: 'UsersPage',
					meta: {
						pageTitle: 'Users Page',
						canAccess: 'users_view',
					},
					component: () => import('@/views/pages/UsersPage.vue'),
				},
				{
					path: 'permissions',
					name: 'permissionsPage',
					meta: {
						pageTitle: 'Permissions Page',
						canAccess: 'permissions_view',
					},
					component: () => import('@/views/pages/PermissionsPage.vue'),
				},
				{
					path: '/programs',
					name: 'ProgramsPage',
					meta: {
						pageTitle: 'Programs Page',
						canAccess: 'programs_view',
					},
					component: () => import('@/views/pages/ProgramsPage.vue'),
				},
				{
					path: '/logs',
					name: 'LogsPage',
					meta: {
						pageTitle: 'Logs Page',
						canAccess: 'logs_view',
					},
					component: () => import('@/views/pages/LogsPage.vue'),
				},
			],
		},
		{
			path: '/login',
			name: 'LoginPage',
			component: () => import('@/views/pages/LoginPage.vue'),
		},
	],
});

router.beforeEach(async (to, from, next) => {
	const isLoggedIn = localStorage.getItem('access_token');

	// Redirect User If Not LoggedIn
	if (to.name !== 'LoginPage' && !isLoggedIn) {
		next({ name: 'LoginPage' });
	} else if (to.name == 'LoginPage' && isLoggedIn) {
		next({ name: 'HomePage' });
	}

	if (isLoggedIn) {
		// Check Access Permission
		const userStore = useUserStore();
		await userStore.getAbilities();
		// let user = JSON.parse(localStorage.getItem('user_data'));
		// let userPermissions = user.permission.permissions.split(',');

		let accessPermission =
			to.meta.canAccess == null
				? true
				: userStore.permissions.filter((permission) => permission == to.meta.canAccess).length > 0;
		if (!accessPermission) {
			next({ name: 'HomePage' });
		}
	}

	return next();
});

export default router;
