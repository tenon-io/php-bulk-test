'use strict';
$.fn.extend({

    getTablePercentages: function () {
        var sum = 0;

        // iterate through each td based on class and add the values
        $(this).find('.data').each(function () {

            var value = $(this).text();

            // add only if the value is number
            if (!isNaN(value) && value.length != 0) {
                sum += parseFloat(value);
            }

        });

        $(this).find('.pct').each(function () {
            var self = $(this);
            var parent = self.parent();
            var out;
            // get reference to this row's .data cell
            var data = parent.find('.data');

            // get the .data cell's value
            var value = parseFloat(data.text());

            // calculate the percentage
            if ((sum < 1) || (value < 1)) {
                out = 0;
            }
            else {
                out = Math.round((value / sum) * 100);
            }

            self.text(out + '%');

        });
        return this;

    }
});


