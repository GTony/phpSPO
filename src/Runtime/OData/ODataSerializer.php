<?php


namespace Office365\PHP\Client\Runtime\OData;
use MyProject\Proxies\__CG__\stdClass;
use Office365\PHP\Client\Runtime\Utilities\JsonConvert;


class ODataSerializer
{

    /**
     * ODataSerializer constructor.
     * @param ODataFormat $format
     */
    function __construct(ODataFormat $format)
    {
        $this->Format = $format;
    }


    /**
     * Generates payload for serialization
     * @param ODataPayload $payload
     * @return string
     */
    public function serialize(ODataPayload $payload)
    {
        $jsonValue = $payload->convertToJson();
        if($this->Format instanceof JsonLightFormat && $this->Format->MetadataLevel == ODataMetadataLevel::Verbose){
            $metadataType = $payload->getEntityTypeName();
            if(substr( $metadataType, 0, 3 ) !== "SP.")
                $metadataType = "SP." . $metadataType;
            $jsonValue["__metadata"] = array("type" => $metadataType);
        }
        if(isset($payload->EntityName)){
            $jsonValue = array( $payload->EntityName => $jsonValue);
        }
        return JsonConvert::serialize($jsonValue);
    }


    /**
     * Deserializes JSON payload
     * @param string $value
     * @param ODataPayload $payload
     */
    public function deserialize($value,ODataPayload $payload)
    {
        $jsonValue = JsonConvert::deserialize($value);

      /*  if(property_exists($jsonValue,"@odata.deltaLink")){
           // $array = get_object_vars($jsonValue);
            $jsonValue->value['deltaLink'] = new \stdClass();
            $jsonValue->value['deltaLink']->{"@odata.deltaLink"} = $jsonValue->{"@odata.deltaLink"};
        }*/

        
        if($this->Format instanceof JsonLightFormat){
            if($this->Format->MetadataLevel == ODataMetadataLevel::Verbose){
                if(property_exists($jsonValue,"d")){
                    $jsonValue = $jsonValue->d;
                }
                if(property_exists($jsonValue,"results")) {
                    $jsonValue = $jsonValue->results;
                }
            }
            else {
                if(property_exists($jsonValue,"value")) {
                    $jsonValue = $jsonValue->value;
                }
            }

        }
        else {
            if($this->Format->MetadataLevel == ODataMetadataLevel::Verbose && property_exists($jsonValue,"value")) {
                $jsonValue = $jsonValue->value;
            }
        }

        if(isset($payload->EntityName)){
            $jsonValue = $jsonValue->{$payload->EntityName};
        }

        $payload->convertFromJson($jsonValue);
    }

    /**
     * @var ODataFormat
     */
    public $Format;

}