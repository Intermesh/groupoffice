/**
 * Plugin for the Ext.Panel class to support a collapsed header title
 * Also implements vertical rotation for east and west border panels
 *
 * @author  Joeri Sebrechts <joeri at sebrechts.net>
 * @version 1.1
 * @date    January 11th, 2010
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 */
Ext.ns('Ext.ux');
Ext.ux.PanelCollapsedTitle = (function() {
  var rotatedCls = 'x-panel-header-rotated';
  var supportsSVG =
    !!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1");
  var patchCollapsedElem = function() {
    var verticalText = ((this.region == 'east') || (this.region == 'west'));
    var containerStyle = 'overflow: visible; padding: 0; border: none; background: none;';
    // For vertical text, and for browsers that support SVG
    // (Firefox, Chrome, Safari 3+, Opera 8+)
    if (verticalText && supportsSVG) {
      this.collapsedHeader = this.ownerCt.layout[this.region].getCollapsedEl().createChild({
        tag: 'div',
        style: 'height: 100%; overflow: hidden;'
      });
      // embed svg code inside this container div
      var SVGNS = 'http://www.w3.org/2000/svg';
      var svg = document.createElementNS(SVGNS, 'svg');
      this.collapsedHeader.dom.appendChild(svg);
      svg.setAttribute('width', '100%');
      svg.setAttribute('height', '100%');
      var textContainer = document.createElementNS(SVGNS, 'text');
      textContainer.setAttribute('x', 6);
      textContainer.setAttribute('y', 1);
      textContainer.setAttribute('transform', 'rotate(90 6 1)');
      textContainer.setAttribute('class', 'x-panel-header ' + rotatedCls);
      svg.appendChild(textContainer);
      this.collapsedHeaderText = document.createTextNode(this.title);
      textContainer.appendChild(this.collapsedHeaderText);
      // set the style to override the unwanted aspects of the x-panel-header class
      // also copy the x-panel-header "color" to "fill", to color the SVG text node
      var color = Ext.fly(textContainer).getStyle('color');
      textContainer.setAttribute('style', containerStyle + ';fill: ' + color + ';');
    // For horizontal text or IE
    } else {
      var titleElemStyle = 'position: relative;';
      if (verticalText) {
        // use writing-mode for vertical text
        titleElemStyle +=
          'white-space: nowrap; writing-mode: tb-rl; top: 1px; left: 3px;';
      } else {
        titleElemStyle += 'top: 2px;';
        // margin-right to ensure no overlap with uncollapse button
        containerStyle += 'padding-left: 4px; margin-right: 18px;';
      };
      this.collapsedHeader = this.ownerCt.layout[this.region].getCollapsedEl().createChild({
        tag: 'div',
        // overrides x-panel-header to remove unwanted aspects
        style: containerStyle,
        cls: 'x-panel-header ' + rotatedCls,
        html: '<span style="'+ titleElemStyle + '">'+this.title+'</span>'
      });
      this.collapsedHeaderText = this.collapsedHeader.first();
    };
    if (this.collapsedIconCls) this.setCollapsedIconClass(this.collapsedIconCls);
  };
  this.init = function(p) {
    if (p.collapsible) {
      var verticalText = ((p.region == 'east') || (p.region == 'west'));
      // update the collapsed header title also
      p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function(t) {
        if (this.rendered && this.collapsedHeaderText) {
          // if the collapsed title element is regular html dom
          if (this.collapsedHeaderText.dom) {
            this.collapsedHeaderText.dom.innerHTML = t;
          // or if this is an SVG text node
          } else if (this.collapsedHeaderText.replaceData) {
            this.collapsedHeaderText.nodeValue = t;
          };
        };
      });
      // update the collapsed icon class also
      p.setCollapsedIconClass = function(cls) {
        var old = this.collapsedIconCls;
        this.collapsedIconCls = cls;
        if(this.rendered && this.collapsedHeader){
          var hd = this.collapsedHeader,
          img = hd.child('img.x-panel-inline-icon');
          // if an icon image is already shown, modify it or remove it
          if(img) {
            if (this.collapsedIconCls) {
              Ext.fly(img).replaceClass(old, this.collapsedIconCls);
            } else {
              // remove img node if the icon class is removed
              Ext.fly(img).remove();
            };
          // otherwise create the img for the icon
          } else if (this.collapsedIconCls) {
            Ext.DomHelper.insertBefore(hd.dom.firstChild, {
              tag:'img', src: Ext.BLANK_IMAGE_URL,
              cls:'x-panel-inline-icon '+this.collapsedIconCls,
              style: verticalText
                ? 'display: block; margin: 1px 2px;'
                : 'margin-top: 2px; margin-right: 4px'
            });
          };
        };
      };
      p.on('render', function() {
        if (this.ownerCt.rendered && this.ownerCt.layout.hasLayout) {
          patchCollapsedElem.call(p);
        } else {
          // the panel's container first needs to render/layout its collapsed title bars
          this.ownerCt.on('afterlayout', patchCollapsedElem, p, {single:true});
        };
      }, p);
    }
  };
  return this;
})();