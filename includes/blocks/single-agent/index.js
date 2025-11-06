/**
 * Single Agent Block
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';

registerBlockType('propertyfinder/single-agent', {
    edit: ({ attributes, setAttributes }) => {
        const blockProps = useBlockProps();
        
        const {
            agentId,
            showImage,
            showBio,
            showContact,
            showSocial,
            showCompliances,
        } = attributes;

        // Get agents for select
        const agents = useSelect((select) => {
            const query = {
                post_type: 'pf_agent',
                per_page: -1,
                orderby: 'title',
                order: 'ASC',
                status: 'publish',
            };
            return select('core').getEntityRecords('postType', 'pf_agent', query) || [];
        }, []);

        // If on single agent page, use current post ID
        const currentPostId = useSelect((select) => {
            return select('core/editor')?.getCurrentPostId?.() || 0;
        }, []);

        const defaultAgentId = agentId || currentPostId;

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('Agent Selection', 'propertyfinder')} initialOpen={true}>
                        <SelectControl
                            label={__('Select Agent', 'propertyfinder')}
                            value={defaultAgentId}
                            options={[
                                { label: __('Use Current Post', 'propertyfinder'), value: 0 },
                                ...(agents.map(agent => ({
                                    label: agent.title?.rendered || __('Untitled', 'propertyfinder'),
                                    value: agent.id,
                                }))),
                            ]}
                            onChange={(value) => setAttributes({ agentId: parseInt(value) || 0 })}
                        />
                    </PanelBody>
                    <PanelBody title={__('Display Options', 'propertyfinder')} initialOpen={true}>
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
                            label={__('Show Contact Info', 'propertyfinder')}
                            checked={showContact}
                            onChange={(value) => setAttributes({ showContact: value })}
                        />
                        <ToggleControl
                            label={__('Show Social Links', 'propertyfinder')}
                            checked={showSocial}
                            onChange={(value) => setAttributes({ showSocial: value })}
                        />
                        <ToggleControl
                            label={__('Show Compliances', 'propertyfinder')}
                            checked={showCompliances}
                            onChange={(value) => setAttributes({ showCompliances: value })}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div className="propertyfinder-single-agent-editor">
                    <h3>{__('Single Agent', 'propertyfinder')}</h3>
                    <ServerSideRender
                        block="propertyfinder/single-agent"
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

