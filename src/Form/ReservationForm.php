<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventName')
            ->add('startTime')
            ->add('endTime')
            ->add('userRelation', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('spaceRelation', EntityType::class, [
                'class' => Space::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
