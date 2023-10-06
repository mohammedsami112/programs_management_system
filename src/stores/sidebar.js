import { defineStore } from 'pinia';

export const useSidebarStore = defineStore('sidebar-store', {
	state: () => ({
		menu: {
			topMenu: {
				items: [
					{
						title: 'Dashboard',
						icon: 'pi pi-home',
						route: 'HomePage',
					},
					{
						title: 'Users',
						icon: 'pi pi-users',
						route: 'UsersPage',
					},
				],
			},
		},
	}),
});
