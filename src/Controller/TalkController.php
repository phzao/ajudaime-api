<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\DonationServiceInterface;
use App\Services\Entity\Interfaces\TalkServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 */
class TalkController extends APIController
{
    /**
     * @Route("/api/v1/talks/{donation_id}", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         $donation_id,
                         DonationServiceInterface $donationService,
                         TalkServiceInterface $talkService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();
            $donation = $donationService->getDonationEntityByIdOrFail($donation_id);

            $talkService->thisTalkGoesBeyondTheUnreadLimitOfOrFail(5,
                                                                   $user->getId(),
                                                                   $donation_id);

            $data["donation"] = $donation->getResume();
            $data["origin"] = $user->getId();

            $talk = $talkService->register($data);
            $talkEntity = $talkService->getTalkEntity();

            $donation->addTalk($talkEntity->getResume());
            $donationUpdated = $donation->getFullDataToUpdateIndex();

            $donationService->update($donationUpdated);

            return $this->respondCreated($talk);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/talks/{uuid}/read", methods={"PUT"})
     * @throws \Exception
     */
    public function talk(Request $request,
                         $uuid,
                         DonationServiceInterface $donationService,
                         TalkServiceInterface $talkService)
    {
        try {
                $talk = $talkService->setTalkRead($uuid);
                $donationService->updateTalk($talk);

//            $user = $this->getUser();
//            $donation = $donationService->getDonationIdOrFail($donation_id);
//
//            $talkService->thisTalkGoesBeyondTheUnreadLimitOfOrFail(5,
//                                                                   $user->getId(),
//                                                                   $donation_id);
//
//            $data["donation"] = $donation;
//            $data["origin"] = $user->getId();
//
//            $talk = $talkService->register($data);

            return $this->respondUpdatedResource();

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}