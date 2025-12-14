<?php

namespace App\Constants;

class Messages
{
    public const INVALID_CREDENTIALS = 'Credenciais inválidas';
    public const INVALID_TOKEN = 'Token inválido ou expirado';
    public const UNAUTHORIZED_ACCESS = 'Acesso negado.';

    public const TRAVEL_ORDER_NOT_FOUND = 'Pedido de viagem não encontrado ou você não tem permissão para acessá-lo';
    public const INVALID_TRAVEL_DATES = 'Data de volta não pode ser antes da ida.';

    public const USER_ALREADY_ADMIN = 'Usuário já é administrador.';
    public const USER_PROMOTED_TO_ADMIN = 'Usuário promovido a administrador com sucesso.';
    public const USER_PROMOTION_FAILED = 'Falha ao promover usuário a administrador.';

    public const LOGOUT_SUCCESS = 'Logout realizado com sucesso';
}
