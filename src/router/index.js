import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: '/',
			component: () => import('@/views/index.vue'),
			children: [
				{
					path: '/',
					name: 'HomePage',
					component: () => import('@/views/pages/HomePage.vue'),
				},
				{
					path: '/users',
					name: 'UsersPage',
					component: () => import('@/views/pages/HomePage.vue'),
				},
				{
					path: '/programs',
					name: 'ProgramsPage',

					component: () => import('@/views/pages/HomePage.vue'),
				},
				{
					path: '/logs',
					name: 'LogsPage',
					component: () => import('@/views/pages/HomePage.vue'),
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

router.beforeEach((to, from, next) => {
	const isLoggedIn = localStorage.getItem('access_token');

	// Redirect User If Not LoggedIn
	if (to.name !== 'LoginPage' && !isLoggedIn) {
		next({ name: 'LoginPage' });
	} else if (to.name == 'LoginPage' && isLoggedIn) {
		next({ name: 'HomePage' });
	}

	return next();
});

export default router;
