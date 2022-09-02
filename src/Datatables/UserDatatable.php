<?php

namespace App\Datatables;

use App\Entity\User;
use Exception;
use Sg\DatatablesBundle\Datatable\AbstractDatatable;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Column\MultiselectColumn;
use Sg\DatatablesBundle\Datatable\Column\VirtualColumn;
use Sg\DatatablesBundle\Datatable\Editable\TextEditable;
use Sg\DatatablesBundle\Datatable\Filter\Select2Filter;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use Sg\DatatablesBundle\Datatable\Style;
use Symfony\Component\HttpFoundation\Request;

class UserDatatable extends AbstractDatatable
{
    /**
     * @throws Exception
     */
    public function buildDatatable(array $options = [])
    {
        $this->ajax->set([
            'method' => Request::METHOD_GET
        ]);

        $this->extensions->set([
            'select' => [
                'blurable' => false,
                'class_name' => 'selected',
                'info' => true,
                'items' => 'row',
                'selector' => 'td, th',
                'style' => 'os',
            ],
            'buttons' => array(
                'show_buttons' => array('copy', 'print'),    // built-in buttons
                'create_buttons' => array(                   // custom buttons
                    array(
                        'action' => array(
                            'template' => 'user/action.js.twig',
                            //'vars' => array('id' => '2', 'test' => 'new value'),
                        ),
                        'text' => 'alert',
                    ),
                    array(
                        'extend' => 'csv',
                        'text' => 'custom csv button',
                    ),
                    array(
                        'extend' => 'pdf',
                        'text' => 'my pdf',
                        'button_options' => array(
                            'exportOptions' => array(
                                'columns' => array('1', '2'),
                            ),
                        ),
                    ),
                ),
            ),
        ]);

        $this->options->set([
            'classes' => Style::BOOTSTRAP_3_STYLE,
            'stripe_classes' => ['strip1', 'strip2', 'strip3'],
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order' => [[0, 'asc']],
            'order_cells_top' => true,
            'global_search_type' => 'like',
            'search_in_non_visible_columns' => true,
        ]);

        $this->columnBuilder
            ->add(null, MultiselectColumn::class, array(
                'start_html' => '<div class="start_checkboxes">',
                'end_html' => '</div>',
                'value' => 'id',
                'value_prefix' => true,
                'render_actions_to_id' => 'sidebar-multiselect-actions', // custom Dom id for the actions
                'actions' => array(
                    array(
                        'route' => 'app_user_bulk_delete',
                        'icon' => 'glyphicon glyphicon-ok',
                        'label' => 'Delete Users',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Delete',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => true,
                        'confirm_message' => 'Really?',
                        'start_html' => '<div class="start_delete_action">',
                        'end_html' => '</div>',
                    ),
                )
            ))
            ->add('id', Column::class, [
                'title' => 'Id',
                'searchable' => true,
                'orderable' => true,
                'type_of_field' => 'integer',
            ])
            ->add('username', Column::class, [
                'title' => 'Title',
                'searchable' => true,
                'orderable' => true,
                'filter' => array(Select2Filter::class, array(
                    'search_type' => 'eq',
                    'cancel_button' => true,
                    'url' => 'select2_usernames',
                )),
                'editable' => array(TextEditable::class, array(
                    'placeholder' => 'Edit value',
                    'empty_text' => 'Empty Text'
                ))
            ])
            ->add(null, ActionColumn::class, [
                'title' => 'Actions',
                'start_html' => '<div class="start_actions">',
                'end_html' => '</div>',
                'actions' => [
                    [
                        'route' => 'app_user_show',
                        'label' => 'Show User',
                        'route_parameters' => [
                            'id' => 'id'
                        ],
                        'attributes' => [
                            'rel' => 'tooltip',
                            'title' => 'Show',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ],
                        'start_html' => '<div class="start_show_action">',
                        'end_html' => null,
                    ],
                    [
                        'route' => 'app_user_edit',
                        'label' => 'Edit User',
                        'route_parameters' => [
                            'id' => 'id'
                        ],
                        'attributes' => [
                            'rel' => 'tooltip',
                            'title' => 'Show',
                            'class' => 'btn btn-warning btn-xs',
                            'role' => 'button'
                        ],
                        'start_html' => null,
                        'end_html' => '</div>',
                    ]
                ]
            ]);
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return User::class;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'user_datatable';
    }
}