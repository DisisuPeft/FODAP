<script setup>
import {useForm} from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed, watch} from "vue";

const props = defineProps({
    modelValue: Boolean,
    curso: Number,
    docente: Number,
    maxWidth: {
        type: String,
        default: '2xl',
    },
})

const formCalificacion = useForm({
    docente_id: props.docente,
    curso_id: props.curso,
    calificacion: null
})


const emit = defineEmits([
    'update:modelValue',
    'form:Calificacion'
])

const submit = () => {
    emit('form:Calificacion', formCalificacion)
}

const closeDialog = () => {
    emit('update:modelValue', false)
}

watch(() => [props.docente, props.curso], ([newDocente, newCurso]) => {
    formCalificacion.docente_id = newDocente;
    formCalificacion.curso_id = newCurso;
    // console.log(newDocente, newCurso)
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="props.modelValue" class="fixed inset-0 overflow-y-auto px-4 py-12 sm:px-0 z-50" scroll-region>
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="props.modelValue" class="fixed inset-0 transform transition-all">
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
                        :class="['mb-6', 'bg-white','rounded-lg', 'overflow-hidden', 'shadow-xl', 'transform', 'transition-all', 'sm:w-full', 'sm:mx-auto', maxWidthClass]"
                    >
                        <div v-if="props.modelValue">
                            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                                <div class="p-4 mt-7 sm:p-8 sm:rounded-lg">
                                    <div class="grid grid-rows-1">
<!--                                        <div class="flex justify-center">-->
                                            <div class="flex justify-center">
                                                <InputLabel for="calificacion"
                                                            value="Unicamente SELECCIONAR si el docente esta APROBADO o NO APROBADO" />
                                            </div>
                                            <div class="flex justify-center p-4">
                                                <v-chip-group
                                                    v-model="formCalificacion.calificacion"
                                                    column
                                                >
                                                    <v-chip color="error">NO APROBADO</v-chip>

                                                    <v-chip color="success">APROBADO</v-chip>
                                                </v-chip-group>
                                            </div>
<!--                                        </div>-->
                                    </div>
                                    <div class="grid grid-rows-1 p-3">
                                        <div class="flex justify-end">
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="flex justify-end">
                                                    <danger-button @click="closeDialog">
                                                        Cerrar
                                                    </danger-button>
                                                </div>
                                                <div  class="flex justify-center">
                                                    <primary-button @click="submit">Crear</primary-button>
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
<!--    <v-dialog max-width="90%" v-model="props.modelValue" persistent>-->

<!--            <v-card width="500" height="300">-->
<!--                <v-card-title>Añadir calificación</v-card-title>-->
<!--                <v-card-text>-->
<!--                    <v-row justify="center">-->
<!--                        <v-col cols="12">-->
<!--                            <InputLabel for="calificacion"-->
<!--                                        value="Unicamente SELECCIONAR si el docente esta APROBADO o NO APROBADO" />-->
<!--                        </v-col>-->
<!--                        <v-col cols="8" class="mt-5 ml-6" align="center">-->
<!--                            <v-chip-group-->
<!--                                v-model="formCalificacion.calificacion"-->
<!--                                column-->
<!--                            >-->
<!--                                <v-chip color="error">NO APROBADO</v-chip>-->

<!--                                <v-chip color="success">APROBADO</v-chip>-->
<!--                            </v-chip-group>-->
<!--                        </v-col>-->
<!--                    </v-row>-->
<!--                </v-card-text>-->
<!--&lt;!&ndash;                <v-card-actions>&ndash;&gt;-->
<!--                    <div class="grid grid-rows-1 p-3">-->
<!--                        <div class="flex justify-end">-->
<!--                            <div class="grid grid-cols-2">-->
<!--                                <div class="flex justify-end">-->
<!--                                    <danger-button @click="closeDialog">-->
<!--                                        Cerrar-->
<!--                                    </danger-button>-->
<!--                                </div>-->
<!--                                <div  class="flex justify-center">-->
<!--                                    <primary-button @click="submit">Subir</primary-button>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--&lt;!&ndash;                </v-card-actions>&ndash;&gt;-->
<!--            </v-card>-->
<!--    </v-dialog>-->
</template>

<style scoped>

</style>
