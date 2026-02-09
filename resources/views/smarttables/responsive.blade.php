@extends('layouts.vertical', ['pageTitle' => 'SmartTables: Responsive Settings'])

@section('css')
@endsection

@section('page-title')
    @include('layouts.partials/app-pagetitle', ['pageTitle'=> 'Responsive Settings', 'pageSubTitle1' => 'Data Visualization', 'pageSubTitle2'=> 'Smart Tables', 'pageSubText' => 'Loading the table with responsive settings & static data.'])
@endsection

@section('content')
    <div class="info-container">
        <strong>Responsive Settings</strong> are enabled by using <code>responsive: { enabled: true, breakpoint: 768,
            columnPriorities: { 0: 1, 1: 2, 2: 3, 3: 4, 4: 5, 5: 6 } }</code>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover d-none" id="myTable">
                <thead>
                <tr>
                    <th>TradeID</th>
                    <th>Symbol</th>
                    <th>Qty</th>
                    <th>BuyPrice</th>
                    <th>SellPrice</th>
                    <th>BuyDate</th>
                    <th>SellDate</th>
                    <th>Profit</th>
                    <th>Fee</th>
                    <th>Net</th>
                    <th>Broker</th>
                    <th>Type</th>
                    <th>Sector</th>
                    <th>Industry</th>
                    <th>MktCap</th>
                    <th>DivYield</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>4V03PG6YJ41</td>
                    <td>RNR^C</td>
                    <td>88</td>
                    <td>$862.69</td>
                    <td>$180.44</td>
                    <td>January 18, 2013</td>
                    <td>September 26, 2015</td>
                    <td>-$60,050.00</td>
                    <td>$97.57</td>
                    <td>-$60,147.57</td>
                    <td>Jabbersphere</td>
                    <td>Sell</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$174,377,450.99</td>
                    <td>3.30%</td>
                </tr>
                <tr>
                    <td>7DJ6J71PN97</td>
                    <td>BFZ</td>
                    <td>6398</td>
                    <td>$416.05</td>
                    <td>$769.28</td>
                    <td>September 21, 2020</td>
                    <td>March 5, 2013</td>
                    <td>$2,260,774.54</td>
                    <td>$27.01</td>
                    <td>$2,260,747.53</td>
                    <td>Zoomdog</td>
                    <td>Sell</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$174,918,140.36</td>
                    <td>9.37%</td>
                </tr>
                <tr>
                    <td>7VQ8AD4QP75</td>
                    <td>DNBF</td>
                    <td>7661</td>
                    <td>$124.73</td>
                    <td>$280.57</td>
                    <td>May 3, 2022</td>
                    <td>May 19, 2007</td>
                    <td>$1,193,747.24</td>
                    <td>$31.03</td>
                    <td>$1,193,716.21</td>
                    <td>Twitterwire</td>
                    <td>Short Sell</td>
                    <td>Finance</td>
                    <td>Major Banks</td>
                    <td>$490,483,854.32</td>
                    <td>5.39%</td>
                </tr>
                <tr>
                    <td>5DJ7A03DM07</td>
                    <td>PSCT</td>
                    <td>1264</td>
                    <td>$573.46</td>
                    <td>$555.79</td>
                    <td>July 27, 2021</td>
                    <td>June 19, 2020</td>
                    <td>-$22,330.88</td>
                    <td>$70.56</td>
                    <td>-$22,401.44</td>
                    <td>Realpoint</td>
                    <td>Short Sell</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$937,480,759.40</td>
                    <td>7.63%</td>
                </tr>
                <tr>
                    <td>5J66FY3KF44</td>
                    <td>XCO</td>
                    <td>9886</td>
                    <td>$560.19</td>
                    <td>$691.93</td>
                    <td>October 30, 2000</td>
                    <td>May 10, 2017</td>
                    <td>$1,301,761.64</td>
                    <td>$59.30</td>
                    <td>$1,301,702.34</td>
                    <td>Blogspan</td>
                    <td>Sell</td>
                    <td>Energy</td>
                    <td>Oil &amp; Gas Production</td>
                    <td>$248,377,509.71</td>
                    <td>5.29%</td>
                </tr>
                <tr>
                    <td>4D93NX6GN89</td>
                    <td>CHMG</td>
                    <td>1947</td>
                    <td>$852.07</td>
                    <td>$333.50</td>
                    <td>September 8, 2011</td>
                    <td>July 3, 2006</td>
                    <td>-$1,009,950.39</td>
                    <td>$53.01</td>
                    <td>-$1,010,003.40</td>
                    <td>Zooveo</td>
                    <td>Buy</td>
                    <td>Finance</td>
                    <td>Major Banks</td>
                    <td>$440,344,370.62</td>
                    <td>4.93%</td>
                </tr>
                <tr>
                    <td>8Y91J07XK92</td>
                    <td>MMM</td>
                    <td>8514</td>
                    <td>$563.82</td>
                    <td>$118.44</td>
                    <td>November 18, 2003</td>
                    <td>December 24, 2011</td>
                    <td>-$3,792,292.92</td>
                    <td>$14.07</td>
                    <td>-$3,792,306.99</td>
                    <td>Voomm</td>
                    <td>Sell</td>
                    <td>Health Care</td>
                    <td>Medical/Dental Instruments</td>
                    <td>$329,511,930.27</td>
                    <td>2.28%</td>
                </tr>
                <tr>
                    <td>6KP5GG2VY09</td>
                    <td>WSFS</td>
                    <td>4447</td>
                    <td>$825.47</td>
                    <td>$559.37</td>
                    <td>November 29, 2004</td>
                    <td>March 9, 2011</td>
                    <td>-$1,182,965.70</td>
                    <td>$85.07</td>
                    <td>-$1,183,050.77</td>
                    <td>Dablist</td>
                    <td>Buy</td>
                    <td>Finance</td>
                    <td>Major Banks</td>
                    <td>$705,999,811.60</td>
                    <td>8.64%</td>
                </tr>
                <tr>
                    <td>6QF6VW0RE55</td>
                    <td>GRBK</td>
                    <td>9460</td>
                    <td>$313.98</td>
                    <td>$190.15</td>
                    <td>September 5, 2019</td>
                    <td>January 22, 2020</td>
                    <td>-$1,171,419.80</td>
                    <td>$24.22</td>
                    <td>-$1,171,444.02</td>
                    <td>Wordify</td>
                    <td>Sell</td>
                    <td>Capital Goods</td>
                    <td>Homebuilding</td>
                    <td>$364,056,899.58</td>
                    <td>5.45%</td>
                </tr>
                <tr>
                    <td>2CU1G24WN29</td>
                    <td>FHY</td>
                    <td>4085</td>
                    <td>$573.20</td>
                    <td>$649.23</td>
                    <td>May 21, 2020</td>
                    <td>August 29, 2015</td>
                    <td>$310,502.05</td>
                    <td>$62.37</td>
                    <td>$310,439.68</td>
                    <td>Photolist</td>
                    <td>Sell</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$776,666,698.32</td>
                    <td>6.34%</td>
                </tr>
                <tr>
                    <td>5Q74YX8AV26</td>
                    <td>SBS</td>
                    <td>3169</td>
                    <td>$544.99</td>
                    <td>$740.63</td>
                    <td>July 21, 2015</td>
                    <td>September 15, 2014</td>
                    <td>$619,856.24</td>
                    <td>$65.94</td>
                    <td>$619,790.30</td>
                    <td>Devcast</td>
                    <td>Sell</td>
                    <td>Public Utilities</td>
                    <td>Water Supply</td>
                    <td>$177,694,103.14</td>
                    <td>9.93%</td>
                </tr>
                <tr>
                    <td>8JE4RC3GV76</td>
                    <td>RTNB</td>
                    <td>1638</td>
                    <td>$555.11</td>
                    <td>$34.63</td>
                    <td>November 27, 2003</td>
                    <td>February 5, 2014</td>
                    <td>-$852,207.84</td>
                    <td>$63.52</td>
                    <td>-$852,271.36</td>
                    <td>Twitterbridge</td>
                    <td>Sell</td>
                    <td>Consumer Services</td>
                    <td>Professional Services</td>
                    <td>$243,368,864.79</td>
                    <td>7.58%</td>
                </tr>
                <tr>
                    <td>2N88GU0ED21</td>
                    <td>LMB</td>
                    <td>1972</td>
                    <td>$571.40</td>
                    <td>$851.56</td>
                    <td>December 15, 2012</td>
                    <td>October 19, 2022</td>
                    <td>$552,395.52</td>
                    <td>$20.92</td>
                    <td>$552,374.60</td>
                    <td>Youspan</td>
                    <td>Short Sell</td>
                    <td>Basic Industries</td>
                    <td>Engineering &amp; Construction</td>
                    <td>$825,435,515.77</td>
                    <td>2.83%</td>
                </tr>
                <tr>
                    <td>8P29T30MJ10</td>
                    <td>NOC</td>
                    <td>4898</td>
                    <td>$263.98</td>
                    <td>$995.03</td>
                    <td>December 7, 2004</td>
                    <td>May 30, 2003</td>
                    <td>$3,581,752.90</td>
                    <td>$54.19</td>
                    <td>$3,581,698.71</td>
                    <td>Realpoint</td>
                    <td>Sell</td>
                    <td>Capital Goods</td>
                    <td>Industrial Machinery/Components</td>
                    <td>$820,633,082.85</td>
                    <td>6.80%</td>
                </tr>
                <tr>
                    <td>8Y61HE8FY65</td>
                    <td>PSB^U</td>
                    <td>2037</td>
                    <td>$706.29</td>
                    <td>$779.57</td>
                    <td>February 11, 2000</td>
                    <td>April 11, 2013</td>
                    <td>$149,165.76</td>
                    <td>$25.15</td>
                    <td>$149,140.61</td>
                    <td>Yakitri</td>
                    <td>Buy</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$597,990,931.75</td>
                    <td>9.14%</td>
                </tr>
                <tr>
                    <td>2NP6NH8NG71</td>
                    <td>THFF</td>
                    <td>6856</td>
                    <td>$546.09</td>
                    <td>$786.17</td>
                    <td>August 16, 2003</td>
                    <td>January 14, 2001</td>
                    <td>$1,645,981.28</td>
                    <td>$22.54</td>
                    <td>$1,645,958.74</td>
                    <td>Kwilith</td>
                    <td>Sell</td>
                    <td>Finance</td>
                    <td>Major Banks</td>
                    <td>$664,298,409.51</td>
                    <td>2.07%</td>
                </tr>
                <tr>
                    <td>6JR0UM9AR33</td>
                    <td>TS</td>
                    <td>4237</td>
                    <td>$929.81</td>
                    <td>$693.05</td>
                    <td>January 3, 2015</td>
                    <td>May 21, 2012</td>
                    <td>-$1,002,512.12</td>
                    <td>$14.26</td>
                    <td>-$1,002,526.38</td>
                    <td>Feedfire</td>
                    <td>Buy</td>
                    <td>Basic Industries</td>
                    <td>Steel/Iron Ore</td>
                    <td>$624,731,020.02</td>
                    <td>5.92%</td>
                </tr>
                <tr>
                    <td>7RW9CD0MU00</td>
                    <td>VRTS</td>
                    <td>7045</td>
                    <td>$462.12</td>
                    <td>$955.04</td>
                    <td>October 3, 2014</td>
                    <td>March 26, 2010</td>
                    <td>$3,472,786.60</td>
                    <td>$95.52</td>
                    <td>$3,472,691.08</td>
                    <td>Yoveo</td>
                    <td>Short Sell</td>
                    <td>Finance</td>
                    <td>Investment Managers</td>
                    <td>$682,163,167.35</td>
                    <td>8.20%</td>
                </tr>
                <tr>
                    <td>8E25EP4EY58</td>
                    <td>USAK</td>
                    <td>2743</td>
                    <td>$249.82</td>
                    <td>$554.92</td>
                    <td>October 4, 2012</td>
                    <td>November 23, 2004</td>
                    <td>$836,097.30</td>
                    <td>$17.55</td>
                    <td>$836,079.75</td>
                    <td>Yodo</td>
                    <td>Sell</td>
                    <td>Transportation</td>
                    <td>Trucking Freight/Courier Services</td>
                    <td>$510,759,967.73</td>
                    <td>1.20%</td>
                </tr>
                <tr>
                    <td>5GF2C48QA96</td>
                    <td>BPTH</td>
                    <td>5978</td>
                    <td>$119.80</td>
                    <td>$529.65</td>
                    <td>October 24, 2020</td>
                    <td>May 4, 2005</td>
                    <td>$2,452,889.30</td>
                    <td>$1.39</td>
                    <td>$2,452,887.91</td>
                    <td>Meembee</td>
                    <td>Short Sell</td>
                    <td>Health Care</td>
                    <td>Major Pharmaceuticals</td>
                    <td>$600,492,028.28</td>
                    <td>8.95%</td>
                </tr>
                <tr>
                    <td>6AD0UK1ER50</td>
                    <td>SWM</td>
                    <td>6654</td>
                    <td>$845.15</td>
                    <td>$838.72</td>
                    <td>August 21, 2018</td>
                    <td>April 4, 2006</td>
                    <td>-$42,848.82</td>
                    <td>$33.58</td>
                    <td>-$42,882.40</td>
                    <td>Jamia</td>
                    <td>Sell</td>
                    <td>Basic Industries</td>
                    <td>Paper</td>
                    <td>$489,158,681.74</td>
                    <td>0.11%</td>
                </tr>
                <tr>
                    <td>5W28TC7XM60</td>
                    <td>BABA</td>
                    <td>1188</td>
                    <td>$659.87</td>
                    <td>$92.82</td>
                    <td>June 20, 2010</td>
                    <td>August 26, 2021</td>
                    <td>-$673,029.60</td>
                    <td>$95.39</td>
                    <td>-$673,124.99</td>
                    <td>Photofeed</td>
                    <td>Sell</td>
                    <td>Miscellaneous</td>
                    <td>Business Services</td>
                    <td>$89,948,943.82</td>
                    <td>2.26%</td>
                </tr>
                <tr>
                    <td>8YF1R57HM21</td>
                    <td>BLIN</td>
                    <td>7097</td>
                    <td>$338.19</td>
                    <td>$199.97</td>
                    <td>March 12, 2007</td>
                    <td>January 13, 2005</td>
                    <td>-$980,427.34</td>
                    <td>$22.12</td>
                    <td>-$980,449.46</td>
                    <td>Cogilith</td>
                    <td>Sell</td>
                    <td>Technology</td>
                    <td>Computer Software: Prepackaged Software</td>
                    <td>$273,347,630.16</td>
                    <td>1.16%</td>
                </tr>
                <tr>
                    <td>5WW6H56VN67</td>
                    <td>SR</td>
                    <td>3629</td>
                    <td>$782.16</td>
                    <td>$963.56</td>
                    <td>August 13, 2004</td>
                    <td>April 21, 2010</td>
                    <td>$658,398.40</td>
                    <td>$55.18</td>
                    <td>$658,343.22</td>
                    <td>Jabbersphere</td>
                    <td>Buy</td>
                    <td>Public Utilities</td>
                    <td>Oil/Gas Transmission</td>
                    <td>$620,372,068.68</td>
                    <td>8.70%</td>
                </tr>
                <tr>
                    <td>4U52DN4UD27</td>
                    <td>VMW</td>
                    <td>8994</td>
                    <td>$424.85</td>
                    <td>$436.77</td>
                    <td>September 20, 2012</td>
                    <td>October 19, 2016</td>
                    <td>$107,107.28</td>
                    <td>$82.95</td>
                    <td>$107,024.33</td>
                    <td>Tavu</td>
                    <td>Short Sell</td>
                    <td>Technology</td>
                    <td>Computer Software: Prepackaged Software</td>
                    <td>$196,295,780.70</td>
                    <td>3.25%</td>
                </tr>
                <tr>
                    <td>2CH5U95EU70</td>
                    <td>RIC</td>
                    <td>8321</td>
                    <td>$250.29</td>
                    <td>$369.41</td>
                    <td>August 16, 2001</td>
                    <td>May 16, 2000</td>
                    <td>$990,251.52</td>
                    <td>$98.62</td>
                    <td>$990,152.90</td>
                    <td>Thoughtworks</td>
                    <td>Sell</td>
                    <td>Basic Industries</td>
                    <td>Precious Metals</td>
                    <td>$657,686,022.62</td>
                    <td>6.53%</td>
                </tr>
                <tr>
                    <td>3YR5V95MY43</td>
                    <td>PSXP</td>
                    <td>122</td>
                    <td>$176.70</td>
                    <td>$729.07</td>
                    <td>January 10, 2007</td>
                    <td>March 17, 2021</td>
                    <td>$67,404.74</td>
                    <td>$53.84</td>
                    <td>$67,350.90</td>
                    <td>Eayo</td>
                    <td>Sell</td>
                    <td>Energy</td>
                    <td>Natural Gas Distribution</td>
                    <td>$170,561,815.55</td>
                    <td>1.31%</td>
                </tr>
                <tr>
                    <td>6WA2PT9NX49</td>
                    <td>FMNB</td>
                    <td>5341</td>
                    <td>$750.23</td>
                    <td>$89.75</td>
                    <td>February 2, 2018</td>
                    <td>November 17, 2015</td>
                    <td>-$3,529,189.88</td>
                    <td>$52.23</td>
                    <td>-$3,529,242.11</td>
                    <td>Bubbletube</td>
                    <td>Buy</td>
                    <td>Finance</td>
                    <td>Major Banks</td>
                    <td>$18,733,078.92</td>
                    <td>0.43%</td>
                </tr>
                <tr>
                    <td>5WN0AU4MK09</td>
                    <td>VNO^I</td>
                    <td>2535</td>
                    <td>$294.04</td>
                    <td>$239.57</td>
                    <td>August 16, 2001</td>
                    <td>February 26, 2011</td>
                    <td>-$138,108.45</td>
                    <td>$53.83</td>
                    <td>-$138,162.28</td>
                    <td>Thoughtstorm</td>
                    <td>Short Sell</td>
                    <td>null</td>
                    <td>null</td>
                    <td>$439,869,983.69</td>
                    <td>1.76%</td>
                </tr>
                <tr>
                    <td>8T04YT7QA61</td>
                    <td>XLRN</td>
                    <td>9398</td>
                    <td>$565.43</td>
                    <td>$453.02</td>
                    <td>August 22, 2021</td>
                    <td>April 19, 2000</td>
                    <td>-$1,056,236.18</td>
                    <td>$81.42</td>
                    <td>-$1,056,317.60</td>
                    <td>Quinu</td>
                    <td>Buy</td>
                    <td>Health Care</td>
                    <td>Biotechnology: Biological Products (No Diagnostic Substances)</td>
                    <td>$907,421,994.16</td>
                    <td>2.17%</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/scripts/pages/smarttableresponsive.js'])
@endsection
