<script setup>
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {computed, onMounted, watch} from "vue";
import axios from "axios";
import {ref} from "vue";
import {Curso} from "@/store/curso.js";


const store = Curso()
const props = defineProps({
    modelValue: Boolean,
    curso: Object,
})
let fecha_I = computed(() => {
    return props.curso.fecha_I.split('-').reverse().join('/')
})
let fecha_F = computed(() => {
    return props.curso.fecha_F.split('-').reverse().join('/')
})
const emit = defineEmits([
    'update:modelValue'
])
const id = ref()
const course = ref({})
onMounted(() => {
    // console.log(props.curso)
})
// console.log(store.curso_Info)
</script>

<template>
    <v-dialog width="auto" v-model="props.modelValue">
        <v-card width="auto" height="700">
            <v-card-title class="text-h6 bg-blue-darken-4">{{ curso.nombreCurso }}</v-card-title>
            <v-card-text>
                <div class="flow-root ... pt-5">
                    <strong>Fecha de realización: </strong>
                    {{fecha_I}} a {{fecha_F}}
<!--                    {{curso.fecha_I.split('-')}}-->
                </div>
                <div class="flow-root ... pt-3">
                    <strong>Asignaturas en la que se requiere formación o actualización: </strong>
                    <v-divider></v-divider>
                    <span class="text-sm">{{curso.asignaturaFA}}</span>
                </div>
                <div class="flow-root ... pt-7">
                    <strong>Contenidos temáticos en que se requiere la formación o actualización: </strong>
                    <v-divider></v-divider>
                    <span class="text-sm">{{curso.contenidosTM}}</span>
                </div>
                <div class="flow-root ... pt-5">
                    <strong>Modalidad: </strong>
                    <template v-if="curso.modalidad === 1">
                        <span class="text-sm">Virtual</span>
                    </template>
                    <template v-if="curso.modalidad === 2">
                        <span class="text-sm">Presencial</span>
                    </template>
                    <template v-if="curso.modalidad === 3">
                        <span class="text-sm">Hibrído</span>
                    </template>

                </div>
                <div class="flow-root ... pt-5">
                    <strong>Periodo en el que se requiere la formación o actualización (enero-junio o agosto diciembre): </strong>
                    <template v-if="curso.periodo === 1">
                        <span class="text-sm">enero-junio</span>
                    </template>
                    <template v-if="curso.periodo === 2  ">
                        <span class="text-sm">agosto-diciembre</span>
                    </template>
                </div>
                <div class="flow-root ... pt-5">
                    <strong>Tipo: </strong>
                    <template v-if="curso.tipo_FDoAP === 1">
                        <span class="text-sm">Formación docente</span>
                    </template>
                    <template v-if="curso.tipo_FDoAP === 2  ">
                        <span class="text-sm">Actualizacion profesional</span>
                    </template>
                </div>
                <div class="flow-root ... pt-5">
                    <strong>Horario: </strong>
                    <span class="text-sm">{{curso.hora_I}} a {{curso.hora_F}}</span>
                </div>
                <div class="flow-root ... pt-5">
                    <strong>Lugar: </strong>
                    <span v-if="curso.lugar" class="text-sm">{{curso.lugar?.nombre}}</span>
                    <span v-if="curso.aula" class="text-sm">{{curso.aula}}</span>
                </div>
                <div class="flow-root ... pt-5">
                    <strong class="text-sm"
                    >Tipo de curso, taller,
                        conferencias, etc:
                    </strong>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 1
                                            "
                    >
                        <span>TALLER</span>
                    </template>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 2
                                            "
                    >
                        <span>CURSO</span>
                    </template>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 3
                                            "
                    >
                        <span>CURSO-TALLER</span>
                    </template>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 4
                                            "
                    >
                        <span>FORO</span>
                    </template>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 5
                                            "
                    >
                        <span>SEMINARIO</span>
                    </template>
                    <template
                        v-if="
                                                props.curso
                                                    .tipo_actividad === 6
                                            "
                    >
                        <span>DIPLOMADO</span>
                    </template>
                </div>

                <template v-if="curso.deteccion_facilitador?.length > 0">
                    <div class="flow-root ... pt-5">
                        <strong>Facilitador(es): </strong>
                        <div v-for="facilitador in curso.deteccion_facilitador">
                            <span>{{facilitador.nombre}}  {{facilitador.apellidoPat}}  {{facilitador.apellidoMat}}</span>
                        </div>
                    </div>
                </template>
                <template v-if="curso.facilitador_externo !== null">
                    <div class="flow-root ... pt-5">
                        <strong>Facilitador(es): </strong>
<!--                        <div v-for="facilitador in store.curso_Info.deteccion_facilitador">-->
                            <span>{{curso.facilitador_externo}}</span>
<!--                        </div>-->
                    </div>
                </template>

            </v-card-text>
            <v-card-actions>
                <v-row justify="center">
                    <v-col  align="center" class="ml-16 pl-16">
                        <v-btn @click="emit('update:modelValue', false)" border
                               flat
                               size="x-large"
                               class="text-none"
                               color="error"
                            >
                            Cerrar
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
