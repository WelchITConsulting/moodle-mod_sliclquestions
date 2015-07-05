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
