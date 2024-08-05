<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import {errorMsg, success_alert} from "@/jsfiels/alertas.js";
import Swal from "sweetalert2";

const props = defineProps({
    user: Object,
    errors: Object,
});
const confirmingUserDeletion = ref(false);
const EmailInput = ref(null);
const message = ref("")
const form = useForm({
    id: props.user.id,
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => EmailInput.value.focus());
};

const deleteUser = () => {
    Swal.fire({
        title: '¿Seguro desea borrar el correo institucional?',
        text: 'Esta acción no se puede revertir',
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        icon: "info",
        timerProgressBar: true
    }).then(res => {
        if (res.isConfirmed){
            form.delete(route('destroy.users'), {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    success_alert('Exito', 'Se elimino el correo institucional.')
                    closeModal()
                },
                onError: () => {
                    errorMsg('Exito', `${format_errors(props.errors)}`)
                    message.value = ""
                },
                onFinish: () => form.reset(),
            });
        }
    })
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
};

const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey]
    }
    return message.value.split('.').join('. ');
}

</script>

<template>
    <section class="space-y-6">
        <DangerButton @click="confirmUserDeletion">
            <v-icon>
                mdi-delete-forever
            </v-icon>
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    ¿Esta seguro que desea borrar la cuenta?
                </h2>

                <div class="mt-6">
                    <InputLabel for="email" value="Correo Institucional" class="sr-only" />

                    <TextInput
                        id="email"
                        ref="EmailInput"
                        v-model="props.user.email"
                        class="mt-1 block w-3/4"
                        placeholder="Correo Institucional"
                        @keyup.enter="deleteUser"
                        disabled
                    />

                    <InputError :message="form.errors.id" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>

                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Borrar Cuenta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
