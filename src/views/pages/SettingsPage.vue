<template>
	<div class="settings-page">
		<div class="personal-information">
			<h2 class="text-2xl font-bold text-slate-600 mb-8">Personal Information</h2>

			<form @submit.prevent="updateProfile()">
				<div class="input-group">
					<label for="name">Name</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.name.$error }"
						type="text"
						id="name"
						placeholder="Enter Your Name"
						v-model="inputs.name"
					/>
					<template v-if="validate.name.$errors">
						<span class="error-msg" v-for="(error, index) in validate.name.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="username">Username</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.username.$error }"
						type="text"
						id="username"
						placeholder="Enter Your Username"
						v-model="inputs.username"
					/>
					<template v-if="validate.username.$errors">
						<span class="error-msg" v-for="(error, index) in validate.username.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<div class="input-group">
					<label for="email">Email</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.email.$error }"
						type="text"
						id="Email"
						placeholder="Enter Your Email"
						v-model="inputs.email"
					/>
					<template v-if="validate.email.$errors">
						<span class="error-msg" v-for="(error, index) in validate.email.$errors" :key="index">
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
						placeholder="Enter Your New Password"
						v-model="inputs.password"
					/>
				</div>
				<div class="input-group">
					<label for="password_confirmation">Password Confirmation</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.password_confirmation.$error }"
						type="password"
						id="password_confirmation"
						placeholder="Confirm Your New Password"
						v-model="inputs.password_confirmation"
					/>
					<template v-if="validate.password_confirmation.$errors">
						<span
							class="error-msg"
							v-for="(error, index) in validate.password_confirmation.$errors"
							:key="index"
						>
							{{ error.$message }}
						</span>
					</template>
				</div>
				<button :disabled="loading" type="submit" class="main-button indigo w-full">
					{{ loading ? 'Loading...' : 'Update' }}
				</button>
			</form>
		</div>

		<div class="general-keys" v-if="$canAccess('programs_general_keys')">
			<h2 class="text-2xl font-bold text-slate-600 mb-8 mt-8">General Keys</h2>

			<div class="input-group">
				<label for="private_key">JWT Signature</label>
				<input readonly :disabled="loading" v-model="keys.jwt" name="jwt_signature" id="jwt_signature" />
			</div>

			<div class="input-group">
				<label for="private_key">Private Key</label>
				<textarea
					readonly
					:disabled="loading"
					v-model="keys.private"
					name="private_key"
					id="private_key"
					cols="30"
					rows="10"
				></textarea>
			</div>

			<div class="input-group">
				<label for="public_key">Public Key</label>
				<textarea
					readonly
					:disabled="loading"
					v-model="keys.public"
					name="public_key"
					id="public_key"
					cols="30"
					rows="10"
				></textarea>
			</div>
		</div>
	</div>
</template>

<script setup>
import { onMounted, reactive, ref, computed, inject } from 'vue';
import { useUserStore } from '@/stores/user';
import { useToast } from 'primevue/usetoast';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, requiredIf, email, sameAs } from '@vuelidate/validators';
import usersApi from '@/controllers/users';
import programsApi from '@/controllers/programs';

const $canAccess = inject('$canAccess');
const loading = ref(false);
const userStore = useUserStore();
const toast = useToast();

const getGeneralKeys = () => {
	programsApi.getGeneralKeys().then((response) => {
		keys.private = response.data.private_key;
		keys.public = response.data.public_key;
		keys.jwt = response.data.jwt_signature;
	});
};

const keys = reactive({
	jwt: null,
	public: null,
	private: null,
});

const inputs = reactive({
	name: null,
	username: null,
	email: null,
	password: null,
	password_confirmation: null,
});

const $externalResults = ref({});

const rules = computed(() => ({
	name: {
		required: helpers.withMessage('Name Is Required', required),
	},
	username: {
		required: helpers.withMessage('Username Is Required', required),
	},
	email: {
		required: helpers.withMessage('email Is Required', required),
		email: helpers.withMessage('Email Is Invalid', email),
	},
	password_confirmation: {
		sameAs: helpers.withMessage('Password Not Match', sameAs(inputs.password)),
	},
}));

const validate = useVuelidate(rules, inputs, { $externalResults });

const updateProfile = () => {
	validate.value.$clearExternalResults();
	validate.value.$touch();
	if (!validate.value.$error) {
		$externalResults.value = {};
		loading.value = true;
		usersApi
			.updateProfile(inputs)
			.then((response) => {
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				localStorage.setItem('user_data', JSON.stringify(response.data));
				userStore.setUserData();
				inputs.password = inputs.password_confirmation = null;
				validate.value.$reset();
			})
			.catch((error) => {
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

onMounted(() => {
	inputs.name = userStore.user.name;
	inputs.email = userStore.user.email;
	inputs.username = userStore.user.username;

	if ($canAccess('programs_general_keys')) {
		getGeneralKeys();
	}
});
</script>
