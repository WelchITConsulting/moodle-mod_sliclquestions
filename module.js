/*
 * Copyright (C) 2015 Welch IT Consulting
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Filename : module.js
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 05 Jul 2015
 */

function depends(selector)
{
    var value = false;
    Y.all(selector).each(function(node){
        if (node.get('checked')) {
            value = node.get('value');
        }
        if (value === '0') {
            Y.all('.kpi-0').show(true);
            Y.all('.kpi-1').hide(true);
            Y.all('.kpi-2').hide(true);
            Y.all('.kpi-3').hide(true);
            Y.all('.kpi-4').hide(true);
        } else if (value === '1') {
            Y.all('.kpi-0').hide(true);
            Y.all('.kpi-1').show(true);
            Y.all('.kpi-2').hide(true);
            Y.all('.kpi-3').hide(true);
            Y.all('.kpi-4').hide(true);
        } else if (value === '2') {
            Y.all('.kpi-0').hide(true);
            Y.all('.kpi-1').hide(true);
            Y.all('.kpi-2').show(true);
            Y.all('.kpi-3').hide(true);
            Y.all('.kpi-4').hide(true);
        } else if (value === '3') {
            Y.all('.kpi-0').hide(true);
            Y.all('.kpi-1').hide(true);
            Y.all('.kpi-2').hide(true);
            Y.all('.kpi-3').show(true);
            Y.all('.kpi-4').hide(true);
        } else if (value === '4') {
            Y.all('.kpi-0').hide(true);
            Y.all('.kpi-1').hide(true);
            Y.all('.kpi-2').hide(true);
            Y.all('.kpi-3').hide(true);
            Y.all('.kpi-4').show(true);
        }
    });
}
Y.all('input[name=kpi_level').on('change', function(e){
    e.preventDefault();
    depends('input[name=kpi_level]:checked');
});
depends('input[name=kpi_level]:checked');

M.mod_sliclquestions = M.mod_sliclquestions || {}

M.mod_sliclquestions.init_sendmessage = function(Y) {
    Y.on('click', function(e){
        Y.all('input.usercheckbox').each(function(){
            this.set('checked', 'checked');
        });
    }, '#checkall');
    Y.on('click', function(e){
        Y.all('input.usercheckbox').each(function(){
            this.set('checked', '');
        });
    }, '#checknone');
    Y.on('click', function(e){
        Y.all('input.usercheckbox').each(function(){
            if (this.get('alt') == 0) {
                this.set('checked', 'checked');
            } else {
                this.set('checked', '');
            }
        });
    }, '#checknotstarted');
    Y.on('click', function(e){
        Y.all('input.usercheckbox').each(function(){
            if (this.get('alt') == 1) {
                this.set('checked', 'checked');
            } else {
                this.set('checked', '');
            }
        });
    }, '#checkstarted');
}
M.mod_sliclquestions.init_reportfilters = function(Y) {
    Y.one('.pupil-sex').delegate('click', function(e){
        url = processUrl();
        alert(url);
    }, 'input[type=radio]');
    Y.one('.school-year').delegate('click', function(e){
        url = processUrl();
        alert(url);
    }, 'input[type=radio]');
}
function processUrl()
{
    var match = location.href.match(/^(https?\:)\/\/(([^:\/?#]*)(?:\:([0-9]+))?)(\/[^?#]*)(\?[^#]*|)(#.*|)$/);
    if (match[6] != '') {
        params = match[6].substr(1).split('&');
        qrystr = '?';
        for(i=0;i<params.length;++i) {
            if ((params[i].substr(0,2) != 'x=') && (params[i].substr(0,2) == 'y=')) {
                qrystr += params[i] + '&';
            }
            qrystr += 'x=' + document.getElementByName('x').valueOf() + '&' +
                      'y=' + document.getElementByName('y').valueOf();
        }
    }
    return match[1] + '//' + match[2] + '/' + match[5] + qrystr + match[7];
}
