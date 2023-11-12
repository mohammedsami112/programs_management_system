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
						accessPermission: null,
					},
					{
						title: 'Users',
						icon: 'pi pi-users',
						route: 'UsersPage',
						accessPermission: 'users_view',
					},
					{
						title: 'Permissions',
						icon: 'pi pi-users',
						route: 'permissionsPage',
						accessPermission: 'permissions_view',
					},
					{
						title: 'Programs',
						icon: 'pi pi-database',
						route: 'ProgramsPage',
						accessPermission: 'programs_view',
					},
					{
						title: 'Logs',
						icon: 'pi pi-sliders-v',
						route: 'LogsPage',
						accessPermission: 'logs_view',
					},
					{
						title: 'Settings',
						icon: 'pi pi-cog',
						route: 'SettingsPage',
						accessPermission: null,
					},
				],
			},
		},
	}),
});
