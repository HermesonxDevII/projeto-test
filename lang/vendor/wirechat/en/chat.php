<?php

return [

    /**-------------------------
     * Chat
     *------------------------*/
    'labels' => [

        'you_replied_to_yourself' => 'Você respondeu a si mesmo',
        'participant_replied_to_you' => ':sender respondeu a Você',
        'participant_replied_to_themself' => ':sender respondeu a si mesmo',
        'participant_replied_other_participant' => ':sender respondeu a :receiver',
        'you' => 'Você',
        'user' => 'Usuário',
        'replying_to' => 'Respondendo para :participant',
        'replying_to_yourself' => 'Respondendo a si mesmo',
        'attachment' => 'Anexo',
    ],

    'inputs' => [
        'message' => [
            'label' => 'Mensagem',
            'placeholder' => 'Digite uma mensagem',
        ],
        'media' => [
            'label' => 'Mídia',
            'placeholder' => 'Mídia',
        ],
        'files' => [
            'label' => 'Arquivos',
            'placeholder' => 'Arquivos',
        ],
    ],

    'message_groups' => [
        'today' => 'Hoje',
        'yesterday' => 'Ontem',

    ],

    'actions' => [
        'open_group_info' => [
            'label' => 'Informações do Grupo',
        ],
        'open_chat_info' => [
            'label' => 'Informações do Chat',
        ],
        'close_chat' => [
            'label' => 'Fechar Chat',
        ],
        'clear_chat' => [
            'label' => 'Limpar Chat',
            'confirmation_message' => 'Tem certeza de que deseja limpar seu histórico de bate-papo? Isso limpará apenas o seu bate-papo e não afetará os outros participantes.',
        ],
        'delete_chat' => [
            'label' => 'Apagar Chat',
            'confirmation_message' => 'Tem certeza de que deseja excluir este chat? Isso removerá o chat apenas do seu lado e não o excluirá para os outros participantes.',
        ],

        'delete_for_everyone' => [
            'label' => 'Apagar para todos',
            'confirmation_message' => 'Você tem certeza?',
        ],
        'delete_for_me' => [
            'label' => 'Apagar pra mim',
            'confirmation_message' => 'Você tem certeza?',
        ],
        'reply' => [
            'label' => 'Responder',
        ],

        'exit_group' => [
            'label' => 'Sair do grupo',
            'confirmation_message' => 'Você tem certeza que deseja sair deste grupo?',
        ],
        'upload_file' => [
            'label' => 'Arquivo',
        ],
        'upload_media' => [
            'label' => 'Fotos & Videos',
        ],
    ],

    'messages' => [

        'cannot_exit_self_or_private_conversation' => 'Não é possível sair da conversa privada ou individual',
        'owner_cannot_exit_conversation' => 'O dono não pode sair da conversa',
        'rate_limit' => 'Muitas requisições!, Por favor espere',
        'conversation_not_found' => 'Conversa não encontrado.',
        'conversation_id_required' => 'O id da conversa é obrigatório',
        'invalid_conversation_input' => 'Entrada de conversa inválida.',
    ],

    /**-------------------------
     * Info Component
     *------------------------*/

    'info' => [
        'heading' => [
            'label' => 'Informações do Chat',
        ],
        'actions' => [
            'delete_chat' => [
                'label' => 'Deletar o Chat',
                'confirmation_message' => 'Tem certeza de que deseja excluir este chat? Isso removerá o chat apenas do seu lado e não o excluirá para os outros participantes.',
            ],
        ],
        'messages' => [
            'invalid_conversation_type_error' => 'Apenas conversas privadas e pessoais são permitidas',
        ],

    ],

    /**-------------------------
     * Group Folder
     *------------------------*/

    'group' => [

        // Group info component
        'info' => [
            'heading' => [
                'label' => 'Informações do Grupo',
            ],
            'labels' => [
                'members' => 'Membros',
                'add_description' => 'Adicionar descrição do grupo',
            ],
            'inputs' => [
                'name' => [
                    'label' => 'Nome do grupo',
                    'placeholder' => 'Digite o nome',
                ],
                'description' => [
                    'label' => 'Descrição',
                    'placeholder' => 'Opcional',
                ],
                'photo' => [
                    'label' => 'Foto',
                ],
            ],
            'actions' => [
                'delete_group' => [
                    'label' => 'Deletar Grupo',
                    'confirmation_message' => 'Você tem certeza que deseja deletar este Grupo ?.',
                    'helper_text' => 'Antes de você poder deletar este grupo, você precisar remover todos os membros.',
                ],
                'add_members' => [
                    'label' => 'Adicionar membros',
                ],
                'group_permissions' => [
                    'label' => 'Permissões de Grupo',
                ],
                'exit_group' => [
                    'label' => 'Sair do grupo',
                    'confirmation_message' => 'Tem certeza de que deseja sair do Grupo ?.',

                ],
            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Somente conversas em grupo são permitidas',
            ],
        ],
        // Members component
        'members' => [
            'heading' => [
                'label' => 'Membros',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Pesquisar',
                    'placeholder' => 'Pesquisar participantes',
                ],
            ],
            'labels' => [
                'members' => 'Membros',
                'owner' => 'Dono',
                'admin' => 'Admin',
                'no_members_found' => 'Nenhum participante encontrado',
            ],
            'actions' => [
                'send_message_to_yourself' => [
                    'label' => 'Mensagem para você mesmo',

                ],
                'send_message_to_member' => [
                    'label' => 'Mensagem :member',

                ],
                'dismiss_admin' => [
                    'label' => 'Remover Admin',
                    'confirmation_message' => 'Você tem certeza que deseja descartar :member como Admin ?.',
                ],
                'make_admin' => [
                    'label' => 'Tornar Admin',
                    'confirmation_message' => 'Você tem certeza que deseja fazer :member um Admin ?.',
                ],
                'remove_from_group' => [
                    'label' => 'Remover',
                    'confirmation_message' => 'Você tem certeza que deseja remover :member desse Grupo ?.',
                ],
                'load_more' => [
                    'label' => 'Carregar mais',
                ],

            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Somente conversas em grupo são permitidas',
            ],
        ],
        // add-Members component
        'add_members' => [
            'heading' => [
                'label' => 'Adicionar Membros',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Procurar',
                    'placeholder' => 'Procurar',
                ],
            ],
            'labels' => [

            ],
            'actions' => [
                'save' => [
                    'label' => 'Salvar',

                ],

            ],
            'messages' => [
                'invalid_conversation_type_error' => 'Somente conversas em grupo são permitidas',
                'members_limit_error' => 'Quantidade de membros excedido',
                'member_already_exists' => 'Ja foi adicionado ao grupo',
            ],
        ],
        // permissions component
        'permisssions' => [
            'heading' => [
                'label' => 'Permissões',
            ],
            'inputs' => [
                'search' => [
                    'label' => 'Pesquisar',
                    'placeholder' => 'Pesquisar',
                ],
            ],
            'labels' => [
                'members_can' => 'Apenas Membros',

            ],
            'actions' => [
                'edit_group_information' => [
                    'label' => 'Editar informação do Grupo',
                    'helper_text' => 'Isso inclui o nome, o ícone e a descrição',
                ],
                'send_messages' => [
                    'label' => 'Enviar Mensagens',
                ],
                'add_other_members' => [
                    'label' => 'Adicionar outros membros',
                ],

            ],
            'messages' => [
            ],
        ],

    ],

];
