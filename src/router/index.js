import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
	history: createWebHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: '/',
			name: 'HomePage',
			component: () => import('@/views/HomeView.vue'),
		},
		{
			path: '/users',
			name: 'UsersPage',
			component: () => import('@/views/HomeView.vue'),
		},
	],
});

export default router;
