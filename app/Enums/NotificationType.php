<?php

namespace App\Enums;

enum NotificationType: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';
    case INFO = 'info';
    case NEW_USER = 'new_user';
    case NEW_ORDER = 'new_order';
    case PAYMENT_RECEIVED = 'payment_received';
    case TASK_ASSIGNED = 'task_assigned';
    case COMMENT_REPLY = 'comment_reply';
    case SYSTEM_UPDATE = 'system_update';

    public function label(): string
    {
        return match($this) {
            self::SUCCESS => 'Sucesso',
            self::WARNING => 'Aviso',
            self::ERROR => 'Erro',
            self::INFO => 'Informação',
            self::NEW_USER => 'Novo Usuário',
            self::NEW_ORDER => 'Novo Pedido',
            self::PAYMENT_RECEIVED => 'Pagamento Recebido',
            self::TASK_ASSIGNED => 'Tarefa Atribuída',
            self::COMMENT_REPLY => 'Resposta de Comentário',
            self::SYSTEM_UPDATE => 'Atualização do Sistema',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::SUCCESS => 'bi-check-circle-fill',
            self::WARNING => 'bi-exclamation-triangle-fill',
            self::ERROR => 'bi-x-circle-fill',
            self::INFO => 'bi-info-circle-fill',
            self::NEW_USER => 'bi-person-plus-fill',
            self::NEW_ORDER => 'bi-cart-fill',
            self::PAYMENT_RECEIVED => 'bi-cash-coin',
            self::TASK_ASSIGNED => 'bi-clipboard-check',
            self::COMMENT_REPLY => 'bi-chat-dots-fill',
            self::SYSTEM_UPDATE => 'bi-arrow-repeat',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SUCCESS => '#28a745',
            self::WARNING => '#ffc107',
            self::ERROR => '#dc3545',
            self::INFO => '#007bff',
            self::NEW_USER => '#6f42c1',
            self::NEW_ORDER => '#fd7e14',
            self::PAYMENT_RECEIVED => '#20c997',
            self::TASK_ASSIGNED => '#17a2b8',
            self::COMMENT_REPLY => '#e83e8c',
            self::SYSTEM_UPDATE => '#6c757d',
        };
    }
}
