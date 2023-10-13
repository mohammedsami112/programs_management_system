<template>
	<Toast position="top-right"></Toast>
	<ConfirmDialog></ConfirmDialog>
	<RouterView />
</template>

<script setup>
import { onMounted } from 'vue';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

onMounted(() => {
	// Set User Data If Already Logging In
	const isLogged = localStorage.getItem('user_data') && localStorage.getItem('access_token');

	if (isLogged) {
		userStore.setUserData();
	}
});
</script>
