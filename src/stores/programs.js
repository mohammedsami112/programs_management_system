import { defineStore } from 'pinia';

export const useProgramsStore = defineStore('programs-store', {
	state: () => ({
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
		usersList: [],
		selectedProgram: {},
		programs: [],
	}),

	actions: {
		setPrograms(payload) {
			this.programs = payload;
		},

		setSelectedProgram(payload) {
			this.selectedProgram = payload;
		},

		setUsersList(payload) {
			this.usersList = payload;
		},
	},
});
