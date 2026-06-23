<?php

function reglas_conciliacion_yape(){
    return [
        [
            'campo' => 'codigo',
            'descripcion' => 'Codigo unico de inscripcion dentro del campo Mensaje del Excel Yape.',
            'prioridad' => 1
        ],
        [
            'campo' => 'monto',
            'descripcion' => 'Monto exacto del pago Yape, sin comision.',
            'prioridad' => 2
        ],
        [
            'campo' => 'numero_operacion',
            'descripcion' => 'Numero de operacion si la plataforma bancaria lo incluye en el Excel o CSV.',
            'prioridad' => 3
        ],
        [
            'campo' => 'origen',
            'descripcion' => 'Nombre mostrado por Yape o banco como origen, comparado de forma parcial con el participante.',
            'prioridad' => 4
        ],
        [
            'campo' => 'fecha_operacion',
            'descripcion' => 'Fecha de operacion reportada por Yape para auditoria.',
            'prioridad' => 5
        ],
        [
            'campo' => 'numero_operacion_yape',
            'descripcion' => 'Numero ingresado por el participante, si tambien aparece en el mensaje o referencia.',
            'prioridad' => 6
        ],
        [
            'campo' => 'revision_manual',
            'descripcion' => 'Si no hay codigo en Mensaje, el administrador confirma coincidencias probables.',
            'prioridad' => 7
        ],
    ];
}
