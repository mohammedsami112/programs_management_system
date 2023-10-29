<template>
	<!-- <button class="main-button w-fit" >Add New User</button> -->
	<i
		class="pi pi-key cursor-pointer text-xl"
		@click="
			keysDialog = true;
			programsStore.setSelectedProgram(props.program);
		"
	></i>

	<Dialog
		v-model:visible="keysDialog"
		modal
		:header="'Keys Of: ' + programsStore.selectedProgram.title"
		class="w-full md:w-[50vw]"
	>
		<div class="input-group">
			<label for="api_token">Api Token</label>
			<input readonly :disabled="loading" v-model="api_token" name="api_token" id="api_token" />
		</div>
		<div class="input-group">
			<label for="private_key">Private Key</label>
			<textarea
				readonly
				:disabled="loading"
				v-model="private_key"
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
				v-model="public_key"
				name="public_key"
				id="public_key"
				cols="30"
				rows="10"
			></textarea>
		</div>
		<button :disabled="loading" type="button" class="main-button indigo" @click="regenerateKeys()">
			{{ loading ? 'Loading...' : 'Regenerate Keys' }}
		</button>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { useProgramsStore } from '@/stores/programs';
import programsApi from '@/controllers/programs';

const props = defineProps(['program']);
const emit = defineEmits(['success']);
const keysDialog = ref(false);
const loading = ref(false);
const programsStore = useProgramsStore();
const toast = useToast();
const confirm = useConfirm();

const private_key = ref(null);
const public_key = ref(null);
const api_token = ref(null);
const programId = ref(null);

watch(
	() => programsStore.selectedProgram,
	(program) => {
		programId.value = program.id;
		api_token.value = program.api_token;
		public_key.value = program.public_key;
		private_key.value = program.private_key;
	}
);

// Regenerate Keys
const regenerateKeys = () => {
	confirm.require({
		message: 'Do You Want To Regenerate This Keys?',
		header: 'Regenerate Keys Confirmation',
		icon: 'pi pi-info-circle',
		acceptClass: 'main-button danger',
		rejectClass: 'main-button default mr-3',
		accept: () => {
			loading.value = true;
			programsApi
				.regenerateKeys(programId.value)
				.then((response) => {
					keysDialog.value = false;
					toast.add({ severity: 'success', detail: response.message, life: 3000 });
					emit('success');
				})
				.finally(() => {
					loading.value = false;
				});
		},
	});
};
</script>
