<template>
	<!-- <button class="main-button indigo w-fit" @click="specificationDialog = true">Add New Permission</button> -->
	<i class="pi pi-user-plus cursor-pointer text-xl" @click="specificationDialog = true"></i>

	<Dialog v-model:visible="specificationDialog" modal header="Manage Specifications" class="w-full md:w-[50vw]">
		<form @submit.prevent="updateSpecifications()">
			<div class="grid gap-4 grid-cols-2 mb-5 mt-5" v-for="(specification, index) in inputs.create" :key="index">
				<div class="input-group">
					<label for="users">Users</label>

					<MultiSelect
						id="users"
						:disabled="loading"
						v-model="inputs.create[index].users"
						:options="usersStore.usersList"
						optionLabel="name"
						optionValue="id"
						placeholder="Select Users"
						class="w-full"
					>
					</MultiSelect>
				</div>
				<div class="input-group">
					<label for="specification">Specification</label>
					<input disabled type="text" id="title" v-model="inputs.create[index].title" />
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Update Specifications' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, requiredIf } from '@vuelidate/validators';
import { useUsersStore } from '@/stores/users';
import { useToast } from 'primevue/usetoast';
import usersApi from '@/controllers/users';

const props = defineProps(['user']);
const emit = defineEmits(['success']);
const specificationDialog = ref(false);
const usersStore = useUsersStore();
const toast = useToast();
const loading = ref(false);

const inputs = reactive({
	create: [
		{
			title: 'Specific Users',
			specifications: 'specific_users',
			users: null,
		},
		{
			title: 'Specific Users Programs',
			specifications: 'specific_programs_users',
			users: null,
		},
		{
			title: 'Specific Users Logs',
			specifications: 'specific_logs_users',
			users: null,
		},
	],
});

watch(
	() => specificationDialog.value,
	(value) => {
		if (value == true) {
			props.user.specification.split(',').forEach((item) => {
				let specific = item.split('-');
				let index = inputs.create.findIndex((inputItem) => inputItem.specifications == specific[0]);
				inputs.create[index].users = specific[1].split('+').map((userId) => parseInt(userId));
			});
		}
	}
);

const updateSpecifications = () => {
	let payload = [];
	inputs.create.forEach((item) => {
		if (item.users != null && item.users.length != 0) {
			let specific = `${item.specifications}-${item.users.join('+')}`;
			payload.push(specific);
		}
	});

	if (payload.length == 0) {
		toast.add({
			severity: 'error',
			detail: 'Choose At Least One User',
			life: 3000,
		});
		return false;
	}

	loading.value = true;
	usersApi
		.updateSpecifications({ user_id: props.user.id, data: payload.join(',') })
		.then((response) => {
			toast.add({
				severity: 'success',
				detail: response.message,
				life: 3000,
			});
			emit('success');
			specificationDialog.value = false;

			inputs.create.forEach((item) => (item.users = null));
		})
		.catch((error) => {
			toast.add({
				severity: 'error',
				detail: error.response.data.message,
				life: 3000,
			});
		})
		.finally(() => {
			loading.value = false;
		});
};
</script>
