const { createElement } = window.wp.element
const { registerFormatType, applyFormat, removeFormat, getActiveFormat } = window.wp.richText
const { InspectorControls, PanelColorSettings } = window.wp.editor;
//import { registerFormatType } from '@wordpress/rich-text';
const { __ } = wp.i18n;
const { withSelect, withDispatch } = wp.data;
const { Component, Fragment } = wp.element;
const { compose } = wp.compose;
//const { InnerBlocks, InspectorControls, RichText, BlockControls, ColorPalette, AlignmentToolbar, BlockAlignmentToolbar, PanelColorSettings } = wp.editor;
const { Button, ButtonGroup, Tooltip, TabPanel, Dashicon, PanelBody, TextControl, RangeControl, ToggleControl, SelectControl, ServerSideRender } = wp.components;


[
    {
        name: 'color',
        title: 'Inline Text Colour'
    },
].forEach(({ name, title }) => {
    const type = `advanced/${name}`;

    registerFormatType(type, {
        title,
        tagName: 'span',
        className: name,
      attributes: {
        style: 'style'
      },
          edit ({ isActive, value, onChange }) {

          //  const { attributes: { selectCareer }, setAttributes } = props;
            let activeColor

            if (isActive) {
              const activeFormat = getActiveFormat(value, type)
              const style = activeFormat.attributes.style

              activeColor = style.replace(new RegExp(`^${name}:\\s*`), '')
            }
            return (

              <InspectorControls>
                  <Fragment>
                      <div className="sw-setting-wrap">
                          <h4>Faisal Alvi</h4>
                      </div>
                    <button
                      className="button button-large button-primary"
                      onClick={
                        (color) => {
                          if ( color ) {
                            onChange( applyFormat( value, {
                              type,
                              attributes: {
                                style: `color:#0073a8`
                              }
                            } ) )
                            return
                          }
                         // onChange( removeFormat( value, type ) )
                        }
                      }
                    >
                      {__( 'Apply' )}

                    </button>
                  </Fragment>

              </InspectorControls>

            )
        }
    })
});
