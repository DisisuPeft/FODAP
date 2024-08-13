<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import {useForm} from "@inertiajs/vue3";
import {computed, onMounted, ref} from "vue";
import {Curso} from "@/store/curso.js";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import CustomSnackBar from "@/Components/CustomSnackBar.vue";
import Loading from "@/Components/Loading.vue";
import {errorMsg, notify, success_alert} from "@/jsfiels/alertas.js";


const message = ref("")
const props = defineProps({
    modelValue: Boolean,
    curso: Number,
    maxWidth: {
        type: String,
        default: '2xl',
    },
});

const form = useForm({
    curso_id: props.curso
})

const loading = ref(false);

const emit = defineEmits([
    'update:modelValue'
]);
const submit = () => {
    loading.value = true;
    form.delete(route('delete.deteccion.desarrollo', props.curso), {
        onSuccess: () => {
            loading.value = false;
            success_alert('Exito.', 'Curso eliminado.')
            emit('update:modelValue', false)
        },
        onError: () => {
            loading.value = false;
            notify('Atención', 'warning', 'El curso no pudo ser eliminado, se debe revisar el codigo.')
        },
    })
}
const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

const closeDialog = () => {
    emit('update:modelValue', false)
}

</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="props.modelValue" class="fixed inset-0 overflow-y-auto px-4 py-16 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="props.modelValue" class="fixed inset-0 top-0 transform transition-all">
                        <div class="absolute inset-0 bg-gray-200 blur-2xl opacity-75" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="props.modelValue"
                        :class="['bg-white','rounded-lg', 'overflow-hidden', 'shadow-xl', 'transform', 'transition-all', 'sm:w-full', 'sm:mx-auto', maxWidthClass]"
                    >
                        <div v-if="props.modelValue">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                                <div class="p-2 mt-2 sm:p-10 sm:rounded-lg">
                                    <div class="grid grid-rows-1">
                                        <!--                                        <div class="flex justify-center">-->
                                        <div class="flex justify-center">
                                            <p class="text-2xl p-2">¿Esta seguro que desea eliminar este curso?</p>
                                        </div>
                                        <!--                                        </div>-->
                                    </div>
                                    <div class="grid grid-rows-1 p-3">
                                        <div class="flex justify-end">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="flex justify-end">
                                                    <primary-button @click="closeDialog">
                                                        Cerrar
                                                    </primary-button>
                                                </div>
                                                <div  class="flex justify-center">
                                                                            <danger-button elevation="5" color="success" @click="submit" class="">
                                                                                Eliminar
                                                                            </danger-button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
<!--    <v-dialog width="auto" v-model="props.modelValue" persistent>-->
<!--        <v-card width="500" height="200">-->
<!--            <v-card-title class="text-center mt-8 mb-10">¿Esta seguro que desea eliminar este curso?</v-card-title>-->
<!--            <v-card-actions>-->
<!--                <v-row justify="center" no-gutters>-->
<!--                    <v-col cols="6" align="center" class="">-->
<!--                        <primary-button elevation="5" color="error" @click="emit('update:modelValue', false)">-->
<!--                            Cerrar-->
<!--                        </primary-button>-->
<!--                    </v-col>-->
<!--                    <v-col cols="2" align="center" class="mr-16">-->
<!--                        <danger-button elevation="5" color="success" @click="submit" class="">-->
<!--                            Eliminar-->
<!--                        </danger-button>-->
<!--                    </v-col>-->
<!--                </v-row>-->
<!--            </v-card-actions>-->
<!--        </v-card>-->
<!--    </v-dialog>-->
    <Loading
    v-model="loading"
    @update:loading="loading = $event"
    >
        <v-fade-transition leave-absolute>
            <v-progress-circular
                v-if="loading"
                color="info"
                :size="64"
                :width="7"
                indeterminate
            ></v-progress-circular>
        </v-fade-transition>
    </Loading>
</template>

<style scoped>

</style>
