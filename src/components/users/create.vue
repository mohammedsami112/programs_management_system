<template>
	<button class="main-button indigo w-fit" @click="createDialog = true">Add New User</button>

	<Dialog v-model:visible="createDialog" modal header="Create New User" class="w-full md:w-[50vw]">
		<form @submit.prevent="createUser()" class="mt-3">
			<!-- <div class="avatar w-full flex justify-center items-center flex-col">
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
			</div> -->
			<div class="grid grid-cols-1 md:grid-cols-2 md:gap-4 mb-5 mt-5">
				<div class="input-group">
					<label for="name">Name</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.name.$error }"
						type="text"
						id="name"
						placeholder="Enter The Name"
						v-model="inputs.create.name"
					/>
					<template v-if="validate.create.name.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.name.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="username">Username</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.username.$error }"
						type="text"
						id="username"
						placeholder="Enter The Username"
						v-model="inputs.create.username"
					/>
					<template v-if="validate.create.username.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.username.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<!-- <div class="input-group">
					<label for="country">Country</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.country.$error }"
						type="text"
						id="country"
						placeholder="Enter The Country"
						v-model="inputs.create.country"
					/>
					<template v-if="validate.create.country.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.country.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="city">City</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.city.$error }"
						type="text"
						id="city"
						placeholder="Enter The City"
						v-model="inputs.create.city"
					/>
					<template v-if="validate.create.city.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.city.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div> -->
				<div class="input-group md:col-span-2">
					<label for="email">Email</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.email.$error }"
						type="text"
						id="email"
						placeholder="Enter The Email"
						v-model="inputs.create.email"
					/>
					<template v-if="validate.create.email.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.email.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="password">Password</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.password.$error }"
						type="password"
						id="password"
						placeholder="Enter The Password"
						v-model="inputs.create.password"
					/>
					<template v-if="validate.create.password.$errors">
						<span class="error-msg" v-for="(error, index) in validate.create.password.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="confirm_password">Confirm Password</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.create.password_confirmation.$error }"
						type="password"
						id="confirm_password"
						placeholder="Confirm Password"
						v-model="inputs.create.password_confirmation"
					/>
					<template v-if="validate.create.password_confirmation.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.create.password_confirmation.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group md:col-span-2">
					<Dropdown
						id="permission"
						v-model="inputs.create.permission"
						:options="usersStore.formInit.permissions"
						:disabled="loading"
						showClear
						optionLabel="title"
						optionValue="id"
						placeholder="Choose User Permission"
						class="w-full"
						:class="{ 'p-invalid': validate.create.permission.$error }"
					/>
					<template v-if="validate.create.permission.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.create.permission.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
			</div>
			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Create New User' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Avatar from 'primevue/avatar';
import FileUpload from 'primevue/fileupload';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, email, sameAs } from '@vuelidate/validators';
import { useUsersStore } from '@/stores/users';
import { useToast } from 'primevue/usetoast';
import usersApi from '@/controllers/users';

const emit = defineEmits(['success']);
const createDialog = ref(false);
const usersStore = useUsersStore();
const toast = useToast();
const loading = ref(false);

const previewAvatar = ref();
const handelUploadingAvatar = (event) => {
	console.log(event);
	previewAvatar.value = event.files[0].objectURL;
	inputs.create.avatar = event.files[0];
};

const inputs = reactive({
	create: {
		name: null,
		username: null,
		email: null,
		password: null,
		password_confirmation: null,
		permission: null,
		avatar: null,
	},
});
const $externalResults = reactive({
	create: {},
});

const rules = computed(() => ({
	create: {
		name: {
			required: helpers.withMessage('Name Is Required', required),
		},
		username: {
			required: helpers.withMessage('Username Is Required', required),
		},

		email: {
			required: helpers.withMessage('Email Is Required', required),
			email: helpers.withMessage('Email Is Invalid', email),
		},
		password: {
			required: helpers.withMessage('Password Is Required', required),
		},
		password_confirmation: {
			required: helpers.withMessage('Password Confirmation Is Required', required),
			sameAs: helpers.withMessage("Password Doesn't Match", sameAs(inputs.create.password)),
		},
		permission: {
			required: helpers.withMessage('Permission Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const createUser = () => {
	validate.value.$clearExternalResults();
	validate.value.create.$touch();
	if (!validate.value.create.$error) {
		$externalResults.create = {};
		loading.value = true;
		usersApi
			.createUser(inputs.create)
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				createDialog.value = false;
				inputs.create.avatar =
					inputs.create.name =
					inputs.create.username =
					inputs.create.country =
					inputs.create.city =
					inputs.create.email =
					inputs.create.password =
					inputs.create.password_confirmation =
					inputs.create.permission =
						null;

				validate.value.create.$reset();
			})
			.catch((error) => {
				console.log(error);
				toast.add({
					severity: 'error',
					detail: error.response.data.message,
					life: 3000,
				});
				$externalResults.create = error.response.data.data;
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
