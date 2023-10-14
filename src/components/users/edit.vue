<template>
	<!-- <button class="main-button w-fit" >Add New User</button> -->
	<i
		class="pi pi-file-edit cursor-pointer text-xl"
		@click="
			editDialog = true;
			usersStore.setSelectedUser(props.user);
		"
	></i>

	<Dialog
		v-model:visible="editDialog"
		modal
		:header="'Edit User: ' + usersStore.selectedUser.username"
		:style="{ width: '50vw' }"
	>
		<form @submit.prevent="editUser()">
			<div class="avatar w-full flex justify-center items-center flex-col">
				<Avatar v-if="!previewAvatar" icon="pi pi-user" size="xlarge" />
				<Avatar v-else :image="previewAvatar" size="xlarge"></Avatar>
				<FileUpload
					class="mt-3"
					mode="basic"
					accept="image/*"
					:maxFileSize="1000000"
					@select="handelUploadingAvatar"
					chooseLabel="Choose Avatar"
				/>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 md:gap-4 mb-5 mt-5">
				<div class="input-group">
					<label for="name">Name</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.name.$error }"
						type="text"
						id="name"
						placeholder="Enter The Name"
						v-model="inputs.edit.name"
					/>
					<template v-if="validate.edit.name.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.name.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="username">Username</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.username.$error }"
						type="text"
						id="username"
						placeholder="Enter The Username"
						v-model="inputs.edit.username"
					/>
					<template v-if="validate.edit.username.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.username.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="country">Country</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.country.$error }"
						type="text"
						id="country"
						placeholder="Enter The Country"
						v-model="inputs.edit.country"
					/>
					<template v-if="validate.edit.country.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.country.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="city">City</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.city.$error }"
						type="text"
						id="city"
						placeholder="Enter The City"
						v-model="inputs.edit.city"
					/>
					<template v-if="validate.edit.city.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.city.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group col-span-2">
					<label for="email">Email</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.email.$error }"
						type="text"
						id="email"
						placeholder="Enter The Email"
						v-model="inputs.edit.email"
					/>
					<template v-if="validate.edit.email.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.email.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="password">Password</label>
					<input
						:disabled="loading"
						type="password"
						id="password"
						placeholder="Enter The Password"
						v-model="inputs.edit.password"
					/>
					<!-- <template v-if="validate.edit.password.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.password.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template> -->
				</div>
				<div class="input-group">
					<label for="confirm_password">Confirm Password</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.edit.password_confirmation.$error }"
						type="password"
						id="confirm_password"
						placeholder="Confirm Password"
						v-model="inputs.edit.password_confirmation"
					/>
					<template v-if="validate.edit.password_confirmation.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.edit.password_confirmation.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group col-span-2">
					<Dropdown
						id="permission"
						v-model="inputs.edit.permission"
						:options="usersStore.formInit.permissions"
						:disabled="loading"
						showClear
						optionLabel="title"
						optionValue="id"
						placeholder="Choose User Permission"
						class="w-full"
						:class="{ 'p-invalid': validate.edit.permission.$error }"
					/>
					<template v-if="validate.edit.permission.$errors">
						<span class="error-msg" v-for="(error, index) in validate.edit.permission.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Update User' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, defineEmits, defineProps, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Avatar from 'primevue/avatar';
import FileUpload from 'primevue/fileupload';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, email, sameAs } from '@vuelidate/validators';
import { useUsersStore } from '@/stores/users';
import { useToast } from 'primevue/usetoast';
import usersApi from '@/controllers/users';

const props = defineProps(['user']);
const emit = defineEmits(['success']);
const editDialog = ref(false);
const usersStore = useUsersStore();
const toast = useToast();
const loading = ref(false);

const previewAvatar = ref();
const handelUploadingAvatar = (event) => {
	console.log(event);
	previewAvatar.value = event.files[0].objectURL;
	inputs.edit.avatar = event.files[0];
};

const inputs = reactive({
	edit: {
		item_id: null,
		name: null,
		username: null,
		country: null,
		city: null,
		email: null,
		password: null,
		password_confirmation: null,
		permission: null,
		avatar: null,
	},
});
const $externalResults = reactive({
	edit: {},
});

watch(
	() => usersStore.selectedUser,
	(user) => {
		inputs.edit.item_id = user.id;
		inputs.edit.name = user.name;
		inputs.edit.username = user.username;
		inputs.edit.country = user.country;
		inputs.edit.city = user.city;
		inputs.edit.email = user.email;
		inputs.edit.permission = user.permission.id;
		previewAvatar.value =
			user.avatar == null ? false : `${import.meta.env.VITE_BACKEND_URL}/storage/${user.avatar}`;
	}
);

const rules = computed(() => ({
	edit: {
		name: {
			required: helpers.withMessage('Name Is Required', required),
		},
		username: {
			required: helpers.withMessage('Username Is Required', required),
		},
		country: {
			required: helpers.withMessage('Country Is Required', required),
		},
		city: {
			required: helpers.withMessage('City Is Required', required),
		},
		email: {
			required: helpers.withMessage('Email Is Required', required),
			email: helpers.withMessage('Email Is Invalid', email),
		},
		// password: {
		// 	required: helpers.withMessage('Password Is Required', required),
		// },
		password_confirmation: {
			// required: helpers.withMessage('Password Confirmation Is Required', required),
			sameAs: helpers.withMessage("Password Doesn't Match", sameAs(inputs.edit.password)),
		},
		permission: {
			required: helpers.withMessage('Permission Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const editUser = () => {
	validate.value.$clearExternalResults();
	validate.value.edit.$touch();
	if (!validate.value.edit.$error) {
		$externalResults.edit = {};
		loading.value = true;
		usersApi
			.editUser(inputs.edit)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				editDialog.value = false;
				inputs.edit.avatar =
					inputs.edit.name =
					inputs.edit.username =
					inputs.edit.country =
					inputs.edit.city =
					inputs.edit.email =
					inputs.edit.password =
					inputs.edit.password_confirmation =
					inputs.edit.permission =
						null;

				validate.value.edit.$reset();
			})
			.catch((error) => {
				console.log(error);
				toast.add({
					severity: 'error',
					detail: error.response.data.message,
					life: 3000,
				});
				$externalResults.edit = error.response.data.data;
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
