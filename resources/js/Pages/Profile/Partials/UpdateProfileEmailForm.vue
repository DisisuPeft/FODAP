<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import { AlertLoading, notify, success_alert } from "@/jsfiels/alertas.js";
import { ref } from "vue";

const props = defineProps({
    user: Object,
    errors: Object,
    from: String,
});

// const user = usePage().props.auth.user;
const message = ref("");

const form = useForm({
    email: props.user.email,
});

const submit = () => {
    Swal.fire({
        title: "¿Ingreso la información correcta?",
        text: "Esta acción se puede revertir",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Confirmar",
        cancelButtonText: "Cancelar",
        icon: "info",
        timerProgressBar: true,
    }).then((res) => {
        if (res.isConfirmed) {
            AlertLoading(
                "Guardando los datos...",
                "Esta accion puede tardar unos minutos"
            );
            form.patch(
                route("profile.email.update", [props.user.id, props.from]),
                {
                    onSuccess: () => {
                        form.reset();
                        success_alert(
                            "Exito",
                            "El correo institucional fue editado con exito."
                        );
                        // router.reload()
                    },
                    onError: () => {
                        // console.log(props.errors)
                        notify(
                            "Alerta",
                            "warning",
                            `${format_errors(props.errors)}`
                        );
                        message.value = "";
                    },
                }
            );
        }
    });
};
const format_errors = (errors) => {
    for (const errorsKey in errors) {
        message.value += errors[errorsKey];
    }
    return message.value.split(".").join(". ");
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Información de perfil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualizar tu correo institucional
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div>
                <InputLabel for="email" value="Correo Institucional" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!--            <div v-if="mustVerifyEmail && user.email_verified_at === null">-->
            <!--                <p class="text-sm mt-2 text-gray-800">-->
            <!--                    Tu correo institucional no esta verificado-->
            <!--                    <Link-->
            <!--                        :href="route('verification.send')"-->
            <!--                        method="post"-->
            <!--                        as="button"-->
            <!--                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"-->
            <!--                    >-->
            <!--                        Has click para reenviar el correo de confirmación.-->
            <!--                    </Link>-->
            <!--                </p>-->

            <!--                <div-->
            <!--                    v-show="status === 'verification-link-sent'"-->
            <!--                    class="mt-2 font-medium text-sm text-green-600"-->
            <!--                >-->
            <!--                    Un nuevo email de verificación fue enviado a tu correo institucional.-->
            <!--                </div>-->
            <!--            </div>-->

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing"
                    >Guardar</PrimaryButton
                >

                <Transition
                    enter-from-class="opacity-0"
                    leave-to-class="opacity-0"
                    class="transition ease-in-out"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
