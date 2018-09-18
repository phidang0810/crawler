<?php

namespace App\Http\Controllers;

use App\Services\QuoteExcel;
use App\Services\ShippingQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    protected $request;

    public function __construct(Request $res)
    {
        $this->request = $res;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function quote(Request $request)
    {
        return view('quote', []);
    }

    private function _validateAndGetShipping($request)
    {
        $this->validate($request, [
            'shipping_zip_code' => 'required',
            'pickup_date' => 'required',
            'order_number' => 'required',
            'shipping_address_type' => 'required',
            'shipping_method' => 'required',
            'pallets' => 'array',
            'pallets.*.num_of_pallet' => 'required|integer|min:0',
            'pallets.*.num_of_carton' => 'required|integer|min:0',
            'pallets.*.length' => 'required|integer|min:0',
            'pallets.*.width' => 'required|integer|min:0',
            'pallets.*.height' => 'required|integer|min:0',
            'pallets.*.weight' => 'required|integer|min:0',
            'pallets.*.freight_class' => 'required|integer',
            'pallets.*.dec_value' => 'required|integer',
        ]);
        $data = $request->only([
            'shipping_zip_code',
            'pickup_date',
            'pallets',
            'order_number',
            'shipping_address_type',
            'shipping_method'
        ]);

        return $data;
    }

    /**
     * @SWG\Swagger(
     *   @SWG\Info(
     *     title="Zuri Quoting version",
     *     version="1.0.0"
     *   )
     * )
     */

    /**
     * @SWG\Post(path="/api/get-quote-fc",
     *   tags={"Quote FC"},
     *   summary="Get quote from FC",
     *   description="",
     *   operationId="quoteFC",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Data parameter",
     *     required=true,
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="shipping_zip_code",
     *         type="string",
     *         example=77006
     *       ),
     *     @SWG\Property(
     *         property="shipping_address_type",
     *         type="string",
     *         description="BUSINESS|RESIDENTIAL",
     *         example="BUSINESS"
     *       ),
     *     @SWG\Property(
     *         property="pickup_date",
     *         type="string",
     *         description="Format required is mm/dd/yyyy",
     *         example="4/20/2018"
     *       ),
     *     @SWG\Property(
     *         property="order_number",
     *         type="string",
     *         example=11111
     *       ),
     *     @SWG\Property(
     *         property="shipping_method",
     *         description="DOCK_TO_DOCK|CURBSIDE|ROOM_OF_CHOOSE|WHITE_GLOVE",
     *         type="string",
     *         example="DOCK_TO_DOCK"
     *       ),
     *     @SWG\Property(
     *         property="pallets",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="num_of_pallet", type="number", example=1),
     *             @SWG\Property(property="num_of_carton", type="number", example=1),
     *             @SWG\Property(property="length", type="number", example=86),
     *             @SWG\Property(property="width", type="number", example=36),
     *             @SWG\Property(property="height", type="number", example=41),
     *             @SWG\Property(property="weight", type="number", example=413),
     *             @SWG\Property(property="dec_value", type="number", example=1207),
     *             @SWG\Property(property="freight_class", type="number", example=50)
     *         )
     *      )
     *
     *     )
     *   ),
     *   @SWG\Response(response="default", description="successful")
     * )
     */
    public function getQuoteFC()
    {
        $input = $this->_validateAndGetShipping($this->request);
        $service = new ShippingQuote($input);
        $data = $service->getFromFC();
        return response()->json(['quotes' => $data['quotes']]);
    }

    /**
     * @SWG\Post(path="/api/get-quote-manna",
     *   tags={"Quote Manna"},
     *   summary="Get quote from Manna",
     *   description="",
     *   operationId="quoteManna",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Data parameter",
     *     required=true,
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="shipping_zip_code",
     *         type="string",
     *         example=77006
     *       ),
     *     @SWG\Property(
     *         property="shipping_address_type",
     *         type="string",
     *         description="BUSINESS|RESIDENTIAL",
     *         example="BUSINESS"
     *       ),
     *     @SWG\Property(
     *         property="pickup_date",
     *         type="string",
     *         description="Format required is mm/dd/yyyy",
     *         example="4/20/2018"
     *       ),
     *     @SWG\Property(
     *         property="order_number",
     *         type="string",
     *         example=11111
     *       ),
     *     @SWG\Property(
     *         property="shipping_method",
     *         description="DOCK_TO_DOCK|CURBSIDE|ROOM_OF_CHOOSE|WHITE_GLOVE",
     *         type="string",
     *         example="DOCK_TO_DOCK"
     *       ),
     *     @SWG\Property(
     *         property="pallets",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="num_of_pallet", type="number", example=1),
     *             @SWG\Property(property="num_of_carton", type="number", example=1),
     *             @SWG\Property(property="length", type="number", example=86),
     *             @SWG\Property(property="width", type="number", example=36),
     *             @SWG\Property(property="height", type="number", example=41),
     *             @SWG\Property(property="weight", type="number", example=413),
     *             @SWG\Property(property="dec_value", type="number", example=1207),
     *             @SWG\Property(property="freight_class", type="number", example=50)
     *         )
     *      )
     *
     *     )
     *   ),
     *   @SWG\Response(response="default", description="successful")
     * )
     */
    public function getQuoteManna()
    {
        $input = $this->_validateAndGetShipping($this->request);
        $service = new ShippingQuote($input);
        $data = $service->getFromManna();
        return response()->json(['quotes' => $data['quotes']]);
    }

    /**
     * @SWG\Post(path="/api/get-quote-convey",
     *   tags={"Quote Convey"},
     *   summary="Get quote from Convey",
     *   description="",
     *   operationId="quoteConvey",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Data parameter",
     *     required=true,
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="shipping_zip_code",
     *         type="string",
     *         example=77006
     *       ),
     *     @SWG\Property(
     *         property="shipping_address_type",
     *         type="string",
     *         description="BUSINESS|RESIDENTIAL",
     *         example="BUSINESS"
     *       ),
     *     @SWG\Property(
     *         property="pickup_date",
     *         type="string",
     *         description="Format required is mm/dd/yyyy",
     *         example="4/20/2018"
     *       ),
     *     @SWG\Property(
     *         property="order_number",
     *         type="string",
     *         example=11111
     *       ),
     *     @SWG\Property(
     *         property="shipping_method",
     *         description="DOCK_TO_DOCK|CURBSIDE|ROOM_OF_CHOOSE|WHITE_GLOVE",
     *         type="string",
     *         example="DOCK_TO_DOCK"
     *       ),
     *     @SWG\Property(
     *         property="pallets",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="num_of_pallet", type="number", example=1),
     *             @SWG\Property(property="num_of_carton", type="number", example=1),
     *             @SWG\Property(property="length", type="number", example=86),
     *             @SWG\Property(property="width", type="number", example=36),
     *             @SWG\Property(property="height", type="number", example=41),
     *             @SWG\Property(property="weight", type="number", example=413),
     *             @SWG\Property(property="dec_value", type="number", example=1207),
     *             @SWG\Property(property="freight_class", type="number", example=50)
     *         )
     *      )
     *
     *     )
     *   ),
     *   @SWG\Response(response="default", description="successful")
     * )
     */
    public function getQuoteConvey()
    {
        $input = $this->_validateAndGetShipping($this->request);
        $service = new ShippingQuote($input);
        $data = $service->getFromConvey();
        return response()->json(['quotes' => $data['quotes']]);
    }

    /**
     * @SWG\Post(path="/api/get-quote-priority",
     *   tags={"Quote Priority 1"},
     *   summary="Get quote from Priority 1",
     *   description="",
     *   operationId="quotePriority",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="Data parameter",
     *     required=true,
     *     @SWG\Schema(
     *       @SWG\Property(
     *         property="shipping_zip_code",
     *         type="string",
     *         example=77006
     *       ),
     *     @SWG\Property(
     *         property="shipping_address_type",
     *         type="string",
     *         description="BUSINESS|RESIDENTIAL",
     *         example="BUSINESS"
     *       ),
     *     @SWG\Property(
     *         property="pickup_date",
     *         type="string",
     *         description="Format required is mm/dd/yyyy",
     *         example="4/20/2018"
     *       ),
     *     @SWG\Property(
     *         property="order_number",
     *         type="string",
     *         example=11111
     *       ),
     *     @SWG\Property(
     *         property="shipping_method",
     *         description="DOCK_TO_DOCK|CURBSIDE|ROOM_OF_CHOOSE|WHITE_GLOVE",
     *         type="string",
     *         example="DOCK_TO_DOCK"
     *       ),
     *     @SWG\Property(
     *         property="pallets",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="num_of_pallet", type="number", example=1),
     *             @SWG\Property(property="num_of_carton", type="number", example=1),
     *             @SWG\Property(property="length", type="number", example=86),
     *             @SWG\Property(property="width", type="number", example=36),
     *             @SWG\Property(property="height", type="number", example=41),
     *             @SWG\Property(property="weight", type="number", example=413),
     *             @SWG\Property(property="dec_value", type="number", example=1207),
     *             @SWG\Property(property="freight_class", type="number", example=50)
     *         )
     *      )
     *
     *     )
     *   ),
     *   @SWG\Response(response="default", description="successful")
     * )
     */
    public function getQuotePriority()
    {
        $input = $this->_validateAndGetShipping($this->request);
        $service = new ShippingQuote($input);
        $data = $service->getFromPriority();
        return response()->json(['quotes' => $data['quotes']]);
    }

    public function export(Request $request)
    {
        $data = $request->get('data-export');

        $data = json_decode($data, true);
        $quote = new QuoteExcel($data);
        $quote->export();
    }
}
