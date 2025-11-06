/**
 * Agent Listing Block
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

registerBlockType('propertyfinder/agent-listing', {
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        
        const {
            postsPerPage,
            columns,
            showImage,
            showBio,
            showEmail,
            showPhone,
            showLinkedIn,
            showRole,
            showStatus,
            enablePagination,
            orderBy,
            order,
        } = attributes;

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Display Settings', 'propertyfinder')} initialOpen={true}>
                        <RangeControl
                            label={__('Posts Per Page', 'propertyfinder')}
                            value={postsPerPage}
                            onChange={(value) => setAttributes({ postsPerPage: value })}
                            min={1}
                            max={50}
                        />
                        <RangeControl
                            label={__('Columns', 'propertyfinder')}
                            value={columns}
                            onChange={(value) => setAttributes({ columns: value })}
                            min={1}
                            max={6}
                        />
                        <SelectControl
                            label={__('Order By', 'propertyfinder')}
                            value={orderBy}
                            options={[
                                { label: __('Title', 'propertyfinder'), value: 'title' },
                                { label: __('Date', 'propertyfinder'), value: 'date' },
                                { label: __('Menu Order', 'propertyfinder'), value: 'menu_order' },
                            ]}
                            onChange={(value) => setAttributes({ orderBy: value })}
                        />
                        <SelectControl
                            label={__('Order', 'propertyfinder')}
                            value={order}
                            options={[
                                { label: __('Ascending', 'propertyfinder'), value: 'ASC' },
                                { label: __('Descending', 'propertyfinder'), value: 'DESC' },
                            ]}
                            onChange={(value) => setAttributes({ order: value })}
                        />
                    </PanelBody>
                    <PanelBody title={__('Metadata Options', 'propertyfinder')} initialOpen={false}>
                        <ToggleControl
                            label={__('Show Image', 'propertyfinder')}
                            checked={showImage}
                            onChange={(value) => setAttributes({ showImage: value })}
                        />
                        <ToggleControl
                            label={__('Show Bio', 'propertyfinder')}
                            checked={showBio}
                            onChange={(value) => setAttributes({ showBio: value })}
                        />
                        <ToggleControl
                            label={__('Show Email', 'propertyfinder')}
                            checked={showEmail}
                            onChange={(value) => setAttributes({ showEmail: value })}
                        />
                        <ToggleControl
                            label={__('Show Phone', 'propertyfinder')}
                            checked={showPhone}
                            onChange={(value) => setAttributes({ showPhone: value })}
                        />
                        <ToggleControl
                            label={__('Show LinkedIn', 'propertyfinder')}
                            checked={showLinkedIn}
                            onChange={(value) => setAttributes({ showLinkedIn: value })}
                        />
                        <ToggleControl
                            label={__('Show Role', 'propertyfinder')}
                            checked={showRole}
                            onChange={(value) => setAttributes({ showRole: value })}
                        />
                        <ToggleControl
                            label={__('Show Status', 'propertyfinder')}
                            checked={showStatus}
                            onChange={(value) => setAttributes({ showStatus: value })}
                        />
                    </PanelBody>
                    <PanelBody title={__('Pagination', 'propertyfinder')} initialOpen={false}>
                        <ToggleControl
                            label={__('Enable Pagination', 'propertyfinder')}
                            checked={enablePagination}
                            onChange={(value) => setAttributes({ enablePagination: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div className="propertyfinder-agent-listing-editor">
                    <h3>{__('Agent Listing', 'propertyfinder')}</h3>
                    <ServerSideRender
                        block="propertyfinder/agent-listing"
                        attributes={attributes}
                    />
                </div>
            </div>
        );
    },
    
    save: () => {
        return null; // Server-side render
    },
});

