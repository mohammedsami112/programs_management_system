import { defineStore } from 'pinia';

export const usePermissionsStore = defineStore('permissions-store', {
	state: () => ({
		permissions_list: [
			{
				label: 'Users',
				items: [
					{
						label: 'View Users Page',
						value: 'users_view',
					},
					{
						label: 'Show His Users Only',
						value: 'users_his_users',
					},
					{
						label: 'Create New Users',
						value: 'users_create',
					},
					{
						label: 'Update Users',
						value: 'users_update',
					},
					{
						label: 'Delete Users',
						value: 'users_delete',
					},
					{
						label: 'Permanently Delete Users',
						value: 'users_force_delete',
					},
					{
						label: 'Restore Users',
						value: 'users_restore',
					},
					{
						label: 'Add Specifications',
						value: 'users_specifications',
					},
				],
			},
			{
				label: 'Permissions',
				items: [
					{
						label: 'View Permissions Page',
						value: 'permissions_view',
					},
					{
						label: 'Create New Permissions',
						value: 'permissions_create',
					},
					{
						label: 'Update Permissions',
						value: 'permissions_update',
					},
					{
						label: 'Delete Permissions',
						value: 'permissions_delete',
					},
					{
						label: 'Permanently Delete Permission',
						value: 'permissions_force_delete',
					},
					{
						label: 'Restore Permissions',
						value: 'permissions_restore',
					},
				],
			},
			{
				label: 'Programs',
				items: [
					{
						label: 'View Programs Page',
						value: 'programs_view',
					},
					{
						label: 'Show His Programs Only',
						value: 'programs_his_programs',
					},
					{
						label: 'Show Programs Keys',
						value: 'programs_access_keys',
					},
					{
						label: 'Add Users To Programs',
						value: 'programs_add_users',
					},
					{
						label: 'Delete Users From Programs',
						value: 'programs_delete_users',
					},
					{
						label: 'Create New Program',
						value: 'programs_create',
					},
					{
						label: 'Update Programs',
						value: 'programs_update',
					},
					{
						label: 'Delete Programs',
						value: 'programs_delete',
					},
					{
						label: 'Permanently Delete Programs',
						value: 'programs_force_delete',
					},
					{
						label: 'Restore Programs',
						value: 'programs_restore',
					},
				],
			},
			{
				label: 'Logs',
				items: [
					{
						label: 'View Logs Page',
						value: 'logs_view',
					},
					{
						label: 'Show All Logs',
						value: 'logs_all',
					},
				],
			},
			// {
			// 	label: 'Specification',
			// 	items: [
			// 		{
			// 			label: 'Specific Users',
			// 			value: 'specific_users',
			// 		},
			// 		{
			// 			label: 'Specific Users Programs',
			// 			value: 'specific_programs_users',
			// 		},
			// 		{
			// 			label: 'Specific Users Logs',
			// 			value: 'specific_logs_users',
			// 		},
			// 	],
			// },
		],
		filters: {
			search: null,
			trash: null,
			sort: {
				column: null,
				sort: null,
			},
			limit: 10,
			page: 1,
		},
		selectedPermission: {},
		permissions: [],
	}),

	actions: {
		setPermissions(payload) {
			this.permissions = payload;
		},

		setSelectedPermission(payload) {
			this.selectedPermission = payload;
		},
	},
});
