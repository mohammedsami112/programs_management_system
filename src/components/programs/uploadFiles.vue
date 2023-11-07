<template>
	<!-- <button class="main-button indigo w-fit" @click="uploadDialog = true">Add New User</button> -->
	<i class="pi pi-upload cursor-pointer text-xl" @click="uploadDialog = true"></i>
	<Dialog
		v-model:visible="uploadDialog"
		modal
		:header="'Upload Files For : ' + props.program.title"
		class="w-full md:w-[50vw]"
	>
		<form @submit.prevent="uploadFiles()" class="mt-3">
			<FileUpload
				class="mb-3 w-full"
				mode="basic"
				accept=".zip"
				:maxFileSize="1000000"
				@select="handelUploadingFile"
				chooseLabel="Choose File"
			/>
			<div class="input-group">
				<template v-if="validate.upload.file.$errors">
					<span class="error-msg" v-for="(error, index) in validate.upload.file.$errors" :key="index">
						{{ error.$message }}
					</span>
				</template>
			</div>

			<button :disabled="loading" type="submit" class="main-button indigo w-full">
				{{ loading ? 'Loading...' : 'Upload Files' }}
			</button>
		</form>
	</Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import Avatar from 'primevue/avatar';
import FileUpload from 'primevue/fileupload';
import { useVuelidate } from '@vuelidate/core';
import { required, helpers, email, sameAs } from '@vuelidate/validators';
import { useUsersStore } from '@/stores/users';
import { useToast } from 'primevue/usetoast';
import programsApi from '@/controllers/programs';

const props = defineProps(['program']);
const emit = defineEmits(['success']);
const uploadDialog = ref(false);
const toast = useToast();
const loading = ref(false);

const handelUploadingFile = (event) => {
	inputs.upload.file = event.files[0];
};

const inputs = reactive({
	upload: {
		file: null,
	},
});

const rules = computed(() => ({
	upload: {
		file: {
			required: helpers.withMessage('File Is Required', required),
		},
	},
}));

const validate = useVuelidate(rules, inputs);

const uploadFiles = () => {
	validate.value.upload.$touch();
	if (!validate.value.upload.$error) {
		loading.value = true;
		programsApi
			.uploadFiles({ file: inputs.upload.file, item_id: props.program.id })
			.then((response) => {
				console.log(response);
				toast.add({
					severity: 'success',
					detail: response.message,
					life: 3000,
				});
				emit('success');
				uploadDialog.value = false;
				inputs.upload.file = null;

				validate.value.upload.$reset();
			})
			.catch((error) => {
				console.log(error);
				toast.add({
					severity: 'error',
					detail: error.response.data.message,
					life: 3000,
				});
			})
			.finally(() => {
				loading.value = false;
			});
	}
};
</script>
