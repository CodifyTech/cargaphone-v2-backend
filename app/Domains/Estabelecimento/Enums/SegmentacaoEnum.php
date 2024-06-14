<?php
    namespace Domains\Estabelecimento\Enums;

    enum SegmentacaoEnum : string {
        case SHOPPING = 'shopping' ;
        case RESTAURANTE = 'restaurante';
        case BAR = 'bar';
        case AEROPORTO = 'aeroporto';
    }
