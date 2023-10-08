<template>
	<div class="login-page">
		<div class="login-card">
			<h2>Login</h2>
			<form @submit.prevent="login()">
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
					<label for="password">Password</label>
					<input
						:disabled="loading"
						:class="{ invalid: validate.password.$error }"
						type="password"
						id="password"
						placeholder="Enter Your Password"
						v-model="inputs.password"
					/>
					<template v-if="validate.password.$errors">
						<span class="error-msg" v-for="(error, index) in validate.password.$errors" :key="index">
							{{ error.$message }}
						</span>
					</template>
				</div>
				<button :disabled="loading" type="submit" class="main-button w-full">
					{{ loading ? 'Loading...' : 'Login' }}
				</button>
			</form>
		</div>
	</div>
</template>

<script setup>
import '@/assets/login.scss';
import { reactive, computed, ref } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers } from '@vuelidate/validators';
import { useToast } from 'primevue/usetoast';
import { useUserStore } from '@/stores/user';
import { useRouter } from 'vue-router';
import AuthApi from '@/controllers/auth';

const loading = ref(false);
const router = useRouter();
const toast = useToast();
const userStore = useUserStore();
const inputs = reactive({
	username: null,
	password: null,
});

const rules = computed(() => ({
	username: {
		required: helpers.withMessage('Username Is Required', required),
	},
	password: {
		required: helpers.withMessage('Password Is Required', required),
	},
}));

const validate = useVuelidate(rules, inputs);

const login = () => {
	validate.value.$touch();

	if (!validate.value.$error) {
		loading.value = true;
		AuthApi.login(inputs)
			.then((response) => {
				userStore.login(response.data);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				router.push({ name: 'HomePage' });
			})
			.catch((error) => {
				toast.add({
					severity: 'error',
					detail: error.response.data.data.error,
					life: 3000,
				});
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
