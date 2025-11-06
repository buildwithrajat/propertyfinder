/**
 * PropertyFinder Blocks Editor JavaScript
 * Simple editor controls using ServerSideRender
 */

(function() {
    'use strict';

    // Check if WordPress blocks are available
    if (typeof wp === 'undefined' || !wp.blocks || !wp.blockEditor) {
        console.error('PropertyFinder Blocks: WordPress blocks API not available');
        return;
    }

    var registerBlockType = wp.blocks.registerBlockType;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;
    var SelectControl = wp.components.SelectControl;
    var ServerSideRender = wp.serverSideRender;
    var __ = wp.i18n.__;

    // Register custom block category
    if (wp.blocks && wp.blocks.registerBlockType) {
        var categories = wp.blocks.getCategories ? wp.blocks.getCategories() : [];
        if (!categories.find(function(cat) { return cat.slug === 'propertyfinder'; })) {
            wp.blocks.setCategories(categories.concat([
                {
                    slug: 'propertyfinder',
                    title: 'PropertyFinder',
                    icon: 'admin-home'
                }
            ]));
        }
    }

    // Agent Listing Block
    if (typeof registerBlockType !== 'undefined') {
        registerBlockType('propertyfinder/agent-listing', {
            title: __('Agent Listing', 'propertyfinder'),
            category: 'propertyfinder',
            icon: 'groups',
            description: __('Display a list of agents with pagination and metadata options.', 'propertyfinder'),
            edit: function(props) {
                var blockProps = useBlockProps();
                var attributes = props.attributes;
                var setAttributes = props.setAttributes;

                return wp.element.createElement(
                    'div',
                    blockProps,
                    wp.element.createElement(
                        InspectorControls,
                        {},
                        wp.element.createElement(
                            PanelBody,
                            { title: __('Display Settings', 'propertyfinder'), initialOpen: true },
                            wp.element.createElement(RangeControl, {
                                label: __('Posts Per Page', 'propertyfinder'),
                                value: attributes.postsPerPage,
                                onChange: function(value) { setAttributes({ postsPerPage: value }); },
                                min: 1,
                                max: 50
                            }),
                            wp.element.createElement(RangeControl, {
                                label: __('Columns', 'propertyfinder'),
                                value: attributes.columns,
                                onChange: function(value) { setAttributes({ columns: value }); },
                                min: 1,
                                max: 6
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Order By', 'propertyfinder'),
                                value: attributes.orderBy,
                                options: [
                                    { label: __('Title', 'propertyfinder'), value: 'title' },
                                    { label: __('Date', 'propertyfinder'), value: 'date' },
                                    { label: __('Menu Order', 'propertyfinder'), value: 'menu_order' }
                                ],
                                onChange: function(value) { setAttributes({ orderBy: value }); }
                            }),
                            wp.element.createElement(SelectControl, {
                                label: __('Order', 'propertyfinder'),
                                value: attributes.order,
                                options: [
                                    { label: __('Ascending', 'propertyfinder'), value: 'ASC' },
                                    { label: __('Descending', 'propertyfinder'), value: 'DESC' }
                                ],
                                onChange: function(value) { setAttributes({ order: value }); }
                            })
                        ),
                        wp.element.createElement(
                            PanelBody,
                            { title: __('Metadata Options', 'propertyfinder'), initialOpen: false },
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Image', 'propertyfinder'),
                                checked: attributes.showImage,
                                onChange: function(value) { setAttributes({ showImage: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Bio', 'propertyfinder'),
                                checked: attributes.showBio,
                                onChange: function(value) { setAttributes({ showBio: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Email', 'propertyfinder'),
                                checked: attributes.showEmail,
                                onChange: function(value) { setAttributes({ showEmail: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Phone', 'propertyfinder'),
                                checked: attributes.showPhone,
                                onChange: function(value) { setAttributes({ showPhone: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show LinkedIn', 'propertyfinder'),
                                checked: attributes.showLinkedIn,
                                onChange: function(value) { setAttributes({ showLinkedIn: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Role', 'propertyfinder'),
                                checked: attributes.showRole,
                                onChange: function(value) { setAttributes({ showRole: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Status', 'propertyfinder'),
                                checked: attributes.showStatus,
                                onChange: function(value) { setAttributes({ showStatus: value }); }
                            })
                        ),
                        wp.element.createElement(
                            PanelBody,
                            { title: __('Pagination', 'propertyfinder'), initialOpen: false },
                            wp.element.createElement(ToggleControl, {
                                label: __('Enable Pagination', 'propertyfinder'),
                                checked: attributes.enablePagination,
                                onChange: function(value) { setAttributes({ enablePagination: value }); }
                            })
                        )
                    ),
                    wp.element.createElement(
                        'div',
                        { className: 'propertyfinder-agent-listing-editor' },
                        wp.element.createElement('h3', {}, __('Agent Listing', 'propertyfinder')),
                        wp.element.createElement(ServerSideRender, {
                            block: 'propertyfinder/agent-listing',
                            attributes: attributes
                        })
                    )
                );
            },
            save: function() {
                return null; // Server-side render
            }
        });

        // Single Agent Block
        registerBlockType('propertyfinder/single-agent', {
            title: __('Single Agent', 'propertyfinder'),
            category: 'propertyfinder',
            icon: 'admin-users',
            description: __('Display a single agent profile with customizable metadata.', 'propertyfinder'),
            edit: function(props) {
                var blockProps = useBlockProps();
                var attributes = props.attributes;
                var setAttributes = props.setAttributes;
                var agents = (typeof propertyfinderBlocks !== 'undefined' && propertyfinderBlocks.agents) ? propertyfinderBlocks.agents : [];

                var agentOptions = [
                    { label: __('Use Current Post', 'propertyfinder'), value: 0 }
                ];

                if (agents && agents.length > 0) {
                    agents.forEach(function(agent) {
                        agentOptions.push({
                            label: agent.label,
                            value: agent.value
                        });
                    });
                }

                return wp.element.createElement(
                    'div',
                    blockProps,
                    wp.element.createElement(
                        InspectorControls,
                        {},
                        wp.element.createElement(
                            PanelBody,
                            { title: __('Agent Selection', 'propertyfinder'), initialOpen: true },
                            wp.element.createElement(SelectControl, {
                                label: __('Select Agent', 'propertyfinder'),
                                value: attributes.agentId,
                                options: agentOptions,
                                onChange: function(value) { setAttributes({ agentId: parseInt(value) || 0 }); }
                            })
                        ),
                        wp.element.createElement(
                            PanelBody,
                            { title: __('Display Options', 'propertyfinder'), initialOpen: true },
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Image', 'propertyfinder'),
                                checked: attributes.showImage,
                                onChange: function(value) { setAttributes({ showImage: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Bio', 'propertyfinder'),
                                checked: attributes.showBio,
                                onChange: function(value) { setAttributes({ showBio: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Contact Info', 'propertyfinder'),
                                checked: attributes.showContact,
                                onChange: function(value) { setAttributes({ showContact: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Social Links', 'propertyfinder'),
                                checked: attributes.showSocial,
                                onChange: function(value) { setAttributes({ showSocial: value }); }
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: __('Show Compliances', 'propertyfinder'),
                                checked: attributes.showCompliances,
                                onChange: function(value) { setAttributes({ showCompliances: value }); }
                            })
                        )
                    ),
                    wp.element.createElement(
                        'div',
                        { className: 'propertyfinder-single-agent-editor' },
                        wp.element.createElement('h3', {}, __('Single Agent', 'propertyfinder')),
                        wp.element.createElement(ServerSideRender, {
                            block: 'propertyfinder/single-agent',
                            attributes: attributes
                        })
                    )
                );
            },
            save: function() {
                return null; // Server-side render
            }
        });
    }
})();

