<?php

return [

    // new-chat component
    'chat' => [
        'labels' => [
            'heading' => ' Novo Chat',
            'you' => 'Você',

        ],

        'inputs' => [
            'search' => [
                'label' => 'Procurar Conversas',
                'placeholder' => 'Procurar',
            ],
        ],

        'actions' => [
            'new_group' => [
                'label' => 'Novo grupo',
            ],

        ],

        'messages' => [

            'empty_search_result' => 'Nenhum usuário encontrado corresponde à sua pesquisa.',
        ],
    ],

    // new-group component
    'group' => [
        'labels' => [
            'heading' => ' Novo Chat',
            'add_members' => ' Adicionar Membros',

        ],

        'inputs' => [
            'name' => [
                'label' => 'Nome do Grupo',
                'placeholder' => 'Digite o Nome',
            ],
            'description' => [
                'label' => 'Descrição',
                'placeholder' => 'Opcional',
            ],
            'search' => [
                'label' => 'Procurar',
                'placeholder' => 'Procurar',
            ],
            'photo' => [
                'label' => 'Foto',
            ],
        ],

        'actions' => [
            'cancel' => [
                'label' => 'Cancelar',
            ],
            'next' => [
                'label' => 'Próximo',
            ],
            'create' => [
                'label' => 'Criar',
            ],

        ],

        'messages' => [
            'members_limit_error' => 'Não pode exceder :count de membros',
            'empty_search_result' => 'Nenhum usuário encontrado correspondendo à sua pesquisa.',
        ],
    ],

];
