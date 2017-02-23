# fuel-economy

API to get vehicle fuel economy from the U.S. E.P.A database off the official U.S. Government website at <a href="http://www.fueleconomy.gov/feg/ws/index.shtml" target="_blank" />fueleconomy.gov</a>.

#

We scrape data from the site instead of using the API since test results have shown that the web API provides different 
and less data than navigating the site as a user.

Here is information on the <a href="http://www.fueleconomy.gov/feg/ws/index.shtml">API provided by fueleconomy.gov</a> if your interested.

## Usage

This PHP REST API provides has a single method: 'get_fueleco'

## Installation

Copy the files into your webserver directory.

## Copyright

Copyright (C) 2016 by André Fortin <andre.v.fortin@gmail.com>

GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
