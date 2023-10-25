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
			<label for="private_key">Private Key</label>
			<textarea v-model="private_key" name="private_key" id="private_key" cols="30" rows="10"></textarea>
		</div>
		<div class="input-group">
			<label for="public_key">Public Key</label>
			<textarea v-model="public_key" name="public_key" id="public_key" cols="30" rows="10"></textarea>
		</div>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, defineEmits, defineProps, watch } from 'vue';
import Dialog from 'primevue/dialog';
import { useProgramsStore } from '@/stores/programs';

const props = defineProps(['program']);
const keysDialog = ref(false);
const programsStore = useProgramsStore();

const private_key = ref(null);
const public_key = ref(null);

watch(
	() => programsStore.selectedProgram,
	(program) => {
		public_key.value = program.public_key;
		private_key.value = program.private_key;
	}
);
</script>
